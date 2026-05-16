use BarryvdhDomPDFFacadePdf;

public function exportInventoryPdf()
{
    $products = Product::with(['category', 'supplier'])
        ->orderBy('category_id')->get();

    $pdf = Pdf::loadView('reports.inventory-pdf', [
        'products' => $products,
        'generated_at' => now()->format('F d, Y h:i A'),
    ]);

    $pdf->setPaper('a4', 'landscape');

    return $pdf->download('inventory-report-' . now()->format('Y-m-d') . '.pdf');
}
