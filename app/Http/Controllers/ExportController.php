<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportController extends Controller
{
    //
    ///export inventories table and download as inventories.xlsx file



    /**
     * Export inventories data to Excel file.
     *
     * Exports inventories data to an Excel file based on the selected year and month.
     *
     * @OA\Post(
     *     path="/api/export/inventories",
     *     tags={"Export"},
     *     summary="Export inventories data to Excel",
     *     description="Export inventories data to an Excel file based on the selected year and month.",
     *     @OA\Response(
     *         response=200,
     *         description="Excel file downloaded successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request. Invalid input data.",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden. User is not authorized to perform this action.",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error. An unexpected error occurred.",
     *     )
     * )
     *
     *
     *
     */



    public function exportInventories(Request $request)
    {
        try {
            // Fetch all inventories
            $inventories = Inventory::where('is_deleted', false)->get();

            // Create a new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setRightToLeft(true);

            // Set headers
            $sheet->setCellValue('A1', 'מזהה שורה');
            $sheet->setCellValue('B1', 'כמות');
            $sheet->setCellValue('C1', 'מק"ט');
            $sheet->setCellValue('D1', 'סוג פריט');
            $sheet->setCellValue('E1', 'פירוט מורחב');
            $sheet->setCellValue('F1', 'נוצר בתאריך');
            $sheet->setCellValue('G1', 'עודכן בתאריך');

            $row = 2;
            foreach ($inventories as $inventory) {
                $sheet->setCellValue('A' . $row, $inventory->id);
                $sheet->setCellValue('B' . $row, $inventory->quantity);
                $sheet->setCellValue('C' . $row, $inventory->sku);
                $sheet->setCellValue('D' . $row, $inventory->item_type);
                $sheet->setCellValue('E' . $row, $inventory->detailed_description);
                $sheet->setCellValue('F' . $row, $inventory->created_at);
                $sheet->setCellValue('G' . $row, $inventory->updated_at);

                $row++;
            }

            // Set & Style the header cells
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'name' => 'Arial',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'D9EAD3'], // Light green fill color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];

            $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);

            // Set & Style the cells
            $cellStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];

            // apply styling to all cells in the sheet
            $sheet->getStyle('A1:O' . ($row - 1))->applyFromArray($cellStyle);

            // set the size for rest of columns
            foreach (range('A', 'O') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // // set the filename as Excel file.
            // $filename = 'inventories.xlsx';
            $filename = 'inventories_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            // Save the file to a temporary location
            $writer = new Xlsx($spreadsheet);
            $writer->save($filename);

            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            return response()->download($filename, 'inventories.xlsx', $headers)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
