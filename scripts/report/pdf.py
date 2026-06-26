
import os
from datetime import datetime
from reportlab.lib import colors
from .utils import log, fail, PALETTE

def generate_pdf(data: dict, summary: str, output_path: str) -> str:
    try:
        from reportlab.lib import colors
        from reportlab.lib.pagesizes import A4
        from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
        from reportlab.lib.units import mm
        from reportlab.platypus import (
            SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle,
            HRFlowable, Image, PageBreak
        )
        from reportlab.lib.enums import TA_CENTER
    except ImportError:
        fail("reportlab package is not installed. Run: pip install reportlab")

    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    log(f"Generating PDF at: {output_path}")
    dark_mode = os.getenv('DARK_MODE', '0') in ('1', 'true', 'True')

    doc = SimpleDocTemplate(
        output_path,
        pagesize=A4,
        rightMargin=25 * mm,
        leftMargin=25 * mm,
        topMargin=20 * mm,
        bottomMargin=20 * mm,
    )

    styles = getSampleStyleSheet()
    style_title = ParagraphStyle(
        'ReportTitle', parent=styles['Title'], fontSize=24, spaceAfter=6,
        textColor=colors.HexColor(PALETTE['primary']), fontName='Helvetica-Bold'
    )
    style_subtitle = ParagraphStyle(
        'ReportSubtitle', parent=styles['Normal'], fontSize=12, spaceAfter=20,
        textColor=colors.HexColor('#6c757d'), fontName='Helvetica'
    )
    style_section_header = ParagraphStyle(
        'SectionHeader', parent=styles['Heading2'], fontSize=14, spaceBefore=20,
        spaceAfter=10, textColor=colors.HexColor(PALETTE['accent']), fontName='Helvetica-Bold'
    )
    style_summary = ParagraphStyle(
        'SummaryText', parent=styles['Normal'], fontSize=11, leading=16,
        spaceAfter=6, textColor=colors.HexColor('#2d3436'), fontName='Helvetica'
    )
    style_metric_value = ParagraphStyle(
        'MetricValue', parent=styles['Normal'], fontSize=18, fontName='Helvetica-Bold',
        textColor=colors.HexColor(PALETTE['primary']), alignment=TA_CENTER
    )
    style_metric_label = ParagraphStyle(
        'MetricLabel', parent=styles['Normal'], fontSize=9, fontName='Helvetica',
        textColor=colors.HexColor('#6c757d'), alignment=TA_CENTER, spaceAfter=4,
    )

    story = []
    period = data['period']
    # Cover page
    logo_path = os.getenv('LOGO_PATH')
    if logo_path and os.path.isfile(logo_path):
        img = Image(logo_path, width=100*mm, preserveAspectRatio=True)
        story.append(img)
        story.append(Spacer(1, 12))
    story.append(Paragraph("Business Performance Report", style_title))
    story.append(Paragraph(
        f"{period['from']} — {period['to']}  |  Generated {datetime.now().strftime('%B %d, %Y at %I:%M %p')}",
        style_subtitle
    ))
    story.append(Spacer(1, 12))
    story.append(HRFlowable(width="100%", thickness=2, color=colors.HexColor(PALETTE['accent']), spaceAfter=15, spaceBefore=5))
    # Metrics Card – display key summary stats
    metrics_data = [
        ['Total Orders', str(data.get('total_orders', 'N/A'))],
        ['Total Revenue', f"${data.get('total_revenue', 0):,.2f}"],
        ['Total Returns', str(data.get('total_returns', 'N/A'))],
        ['Total Refunds', f"${data.get('total_refunds', 0):,.2f}"],
        ['Return Rate', f"{data.get('return_rate_pct', 0)}%"],
    ]
    metrics_table = Table(metrics_data, colWidths=[80*mm, 80*mm])
    metrics_table.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor(PALETTE['primary'])),
        ('TEXTCOLOR', (0, 0), (-1, 0), colors.white),
        ('ALIGN', (0, 0), (-1, -1), 'CENTER'),
        ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
        ('FONTSIZE', (0, 0), (-1, -1), 12),
        ('BOTTOMPADDING', (0, 0), (-1, 0), 6),
        ('BACKGROUND', (0, 1), (-1, -1), colors.HexColor('#f8f9fa')),
        ('GRID', (0, 0), (-1, -1), 0.5, colors.gray),
    ]))
    story.append(metrics_table)
    story.append(Spacer(1, 15))
    # Executive Summary
    story.append(Paragraph("Executive Summary", style_section_header))
    story.append(HRFlowable(width="100%", thickness=1, color=colors.HexColor('#e0e0e0'), spaceAfter=10))
    for para in summary.strip().split('\n\n'):
        para = para.strip()
        if para:
            story.append(Paragraph(para, style_summary))
            story.append(Spacer(1, 6))
    story.append(Spacer(1, 10))
    # Revenue by Category Table and Chart
    if data.get('revenue_by_category'):
        story.append(Paragraph("Revenue by Category", style_section_header))
        # Table generation would be copied here
        chart = create_revenue_bar_chart(data['revenue_by_category'])
        story.append(chart)
        story.append(Spacer(1, 15))
    # Top Products Table
    if data.get('top_products'):
        story.append(Paragraph("Top Products by Revenue", style_section_header))
        prod_header = ['#', 'Product', 'SKU', 'Revenue', 'Orders']
        prod_rows = [prod_header]
        for i, p in enumerate(data['top_products'], 1):
            prod_rows.append([
                str(i),
                str(p.get('name', '—')),
                str(p.get('sku', '—')),
                f"${p.get('revenue', 0):,.2f}",
                str(p.get('orders', '—')),
            ])
        prod_table = Table(prod_rows, colWidths=[
            (A4[0] - 50 * mm) * 0.06,
            (A4[0] - 50 * mm) * 0.34,
            (A4[0] - 50 * mm) * 0.20,
            (A4[0] - 50 * mm) * 0.22,
            (A4[0] - 50 * mm) * 0.18,
        ])
        prod_table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor(PALETTE['accent'])),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.white),
            ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
            ('FONTSIZE', (0, 0), (-1, 0), 10),
            ('ALIGN', (0, 0), (-1, 0), 'CENTER'),
            ('FONTNAME', (0, 1), (-1, -1), 'Helvetica'),
            ('FONTSIZE', (0, 1), (-1, -1), 9),
            ('ALIGN', (0, 1), (0, -1), 'CENTER'),
            ('ALIGN', (1, 1), (1, -1), 'LEFT'),
            ('ALIGN', (2, 1), (2, -1), 'LEFT'),
            ('ALIGN', (3, 1), (-1, -1), 'RIGHT'),
            ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, colors.HexColor('#f8f9fa')]),
            ('GRID', (0, 0), (-1, -1), 0.5, colors.HexColor('#dee2e6')),
            ('TOPPADDING', (0, 0), (-1, -1), 8),
            ('BOTTOMPADDING', (0, 0), (-1, -1), 8),
            ('LEFTPADDING', (0, 0), (-1, -1), 10),
            ('RIGHTPADDING', (0, 0), (-1, -1), 10),
        ]))
        story.append(prod_table)
        story.append(Spacer(1, 15))
    def add_page_number(canvas, doc):
        canvas.saveState()
        if dark_mode:
            canvas.setFillColor(colors.HexColor(PALETTE['background']))
            canvas.rect(0, 0, doc.pagesize[0], doc.pagesize[1], fill=1, stroke=0)
            canvas.setFillColor(colors.black)
        page_num = canvas.getPageNumber()
        canvas.setFont('Helvetica', 9)
        canvas.drawRightString(doc.pagesize[0] - 25 * mm, 15 * mm, f"Page {page_num}")
        canvas.restoreState()
    doc.build(story, onFirstPage=add_page_number, onLaterPages=add_page_number)
    log(f"PDF generated successfully: {output_path}")
    return output_path

def create_revenue_bar_chart(categories):
    from reportlab.graphics.shapes import Drawing
    from reportlab.graphics.charts.barcharts import VerticalBarChart
    values = [cat.get('revenue', 0) for cat in categories]
    labels = [cat.get('category', '') for cat in categories]
    drawing = Drawing(width=400, height=200)
    bc = VerticalBarChart()
    bc.x = 50
    bc.y = 30
    bc.height = 150
    bc.width = 300
    bc.data = [values]
    bc.categoryAxis.categoryNames = labels
    bc.barWidth = 20
    bc.groupSpacing = 10
    bc.barSpacing = 5
    bc.valueAxis.labelTextFormat = lambda v: f"${v:,.0f}"
    bc.bars[0].fillColor = colors.HexColor(PALETTE['accent'])
    drawing.add(bc)
    return drawing
