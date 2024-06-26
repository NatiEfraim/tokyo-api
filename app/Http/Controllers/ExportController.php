<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\Inventory;
use App\Models\User;
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

    /**
     * Export inventories data to Excel file.
     *
     * Exports inventories data to an Excel file based on the selected year and month.
     *
     * @OA\Get(
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

    ///export inventories table and download as inventories.xlsx file
    public function exportInventories()
    {
        try {
  
            // Fetch all inventories records
            $inventories = Inventory::with(['itemType'])
                ->where('is_deleted', false)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($inventory) {

                    // Format the created_at and updated_at timestamps
                    $inventory->created_at_date = optional($inventory->created_at)->format('d/m/Y') ?? null;
                    $inventory->updated_at_date = optional($inventory->updated_at)->format('d/m/Y') ?? null;
                    $inventory->available = $inventory->quantity - $inventory->reserved;

                return $inventory;
                });

            // Create a new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setRightToLeft(true);

            // Set headers
            $sheet->setCellValue('A1', 'מזהה שורה');
            $sheet->setCellValue('B1', 'מלאי זמין');
            $sheet->setCellValue('C1', 'מק"ט');
            $sheet->setCellValue('D1', 'סוג פריט');
            $sheet->setCellValue('E1', 'פירוט מורחב');
            $sheet->setCellValue('F1', 'שמורים');
            $sheet->setCellValue('G1', 'נוצר בתאריך');
            $sheet->setCellValue('H1', 'עודכן בתאריך');

            $row = 2;
            foreach ($inventories as $inventory) {

                $sheet->setCellValue('A' . $row, $inventory->id ?? 'לא קיים');
                $sheet->setCellValue('B' . $row, $inventory->available ?? 'לא קיים');
                $sheet->setCellValue('C' . $row, $inventory->sku ?? 'לא קיים');
                $sheet->setCellValue('D' . $row, $inventory->itemType->type ?? 'לא קיים');
                $sheet->setCellValue('E' . $row, $inventory->detailed_description ?? 'לא קיים');
                $sheet->setCellValue('F' . $row, $inventory->reserved ?? 'לא קיים');
                $sheet->setCellValue('G' . $row, $inventory->created_at_date ?? 'לא קיים');
                $sheet->setCellValue('H' . $row, $inventory->created_at_date ?? 'לא קיים');



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

            $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

            // Set & Style the cells
            $cellStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];

            // apply styling to all cells in the sheet
            $sheet->getStyle('A1:H' . ($row - 1))->applyFromArray($cellStyle);

            // set the size for rest of columns
            foreach (range('A', 'H') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $fileName = 'inventories_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            $writer = new Xlsx($spreadsheet);

            // Save the file to a temporary location
            $writer->save(storage_path('app/' .   $fileName));

            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"$fileName\"",
            ];

            return response()->download(storage_path('app/' .  $fileName), $fileName, $headers)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {

            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Export users data to Excel file.
     *
     * Exports users data to an Excel file based on the selected year and month.
     *
     * @OA\Get(
     *     path="/api/export/users",
     *     tags={"Export"},
     *     summary="Export users data to Excel",
     *     description="Export users data to an Excel file based on the selected year and month.",
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

    public function exportUsers(Request $request)
    {
        try {


            // Fetch users_fetch
            $users = User::where('is_deleted', false)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($user) {

                    // Format the created_at and updated_at timestamps
                    $user->created_at_date = optional($user->created_at)->format('d/m/Y')??null;
                    $user->updated_at_date = optional($user->updated_at)->format('d/m/Y')??null;

                    return $user;
                });


            // Set a spreadsheet instance
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set direction from right to left
            $sheet->setRightToLeft(true);

            // Set headers
            $sheet->setCellValue('A1', 'מזהה שורה');
            $sheet->setCellValue('B1', 'שם משתמש');
            $sheet->setCellValue('C1', 'מספר אישי');
            $sheet->setCellValue('D1', 'מייל');
            $sheet->setCellValue('E1', 'מספר טלפון');
            $sheet->setCellValue('F1', 'סוג עובד');
            $sheet->setCellValue('G1', 'נוצר בתאריך');
            $sheet->setCellValue('H1', 'עודכן בתאריך');

            $row = 2;
            foreach ($users as $user) {



                $sheet->setCellValue('A' . $row, $user->id ?? 'לא קיים');
                $sheet->setCellValue('B' . $row, $user->name ?? 'לא קיים');
                $sheet->setCellValue('C' . $row, $user->personal_number ?? 'לא קיים');
                $sheet->setCellValue('D' . $row, $user->email ?? 'לא קיים');
                $sheet->setCellValue('E' . $row, $user->phone ?? 'לא קיים');
                $sheet->setCellValue('F' . $row, $user->emp_type_id ? $user->translated_employee_type : 'לא קיים');
                $sheet->setCellValue('G' . $row, $user->created_at_date ?? 'לא קיים');
                $sheet->setCellValue('H' . $row, $user->updated_at_date ?? 'לא קיים');

                $row++;
            }


            // set & style the header cells
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

            //set header style
            $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

            // Set & Style the cells
            $cellStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];

            // Apply styling to all cells in the sheet
            $sheet->getStyle('A1:H' . ($row - 1))->applyFromArray($cellStyle);

            // Set auto size for columns
            foreach (range('A', 'H') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }


            // create Excel file
            $writer = new Xlsx($spreadsheet);

            $fileName = 'users_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            $writer->save(storage_path('app/' .   $fileName));

            $headers = [
                "Content-Type" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "Content-Disposition" => "attachment; filename=\"$fileName\""
            ];


            return response()->download(storage_path('app/' .  $fileName), $fileName, $headers)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת, יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }




    /**
     * @OA\Get(
     *     path="/api/export/distributions",
     *     summary="Export distributions",
     *     description="Export distributions data based on provided filters",
     *     tags={"Export"},
     *     @OA\Parameter(
     *         name="sku",
     *         in="query",
     *         description="SKU of the inventory",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status of the distribution (0: pending, 1: approved, 2: canceled)",
     *         @OA\Schema(type="integer", format="int32", minimum=0, maximum=2)
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Name of the department",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="personal_number",
     *         in="query",
     *         description="Personal number of the creator",
     *         @OA\Schema(type="string", minLength=1, maxLength=7)
     *     ),
     *     @OA\Parameter(
     *         name="created_at",
     *         in="query",
     *         description="Creation date of the distribution (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="updated_at",
     *         in="query",
     *         description="Last updated date of the distribution (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Email sent successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="OK")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid data was sent")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Error sending email")
     *          )
     *      )
     * )
     */

    public function exportDistributions()
    {
        try {
     
            // //? fetch all distributions records along with relations.

            // Fetch all distributions records.
            $distributions = Distribution::with([ 'itemType','createdForUser'])
                ->where('is_deleted', 0)
                ->orderBy('created_at', 'desc')
                ->get()

                ->map(function ($distribution) {

                    // Format the created_at and updated_at timestamps

                    $distribution->created_at_date = optional($distribution->created_at)->format('d/m/Y')??null;

                    $distribution->updated_at_date = optional($distribution->updated_at)->format('d/m/Y')??null;


                    return $distribution;
                });


            // Create a new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setRightToLeft(true);

            // Set the header row for the Excel sheet
            $sheet->setCellValue('A1', 'מזהה שורה');
            $sheet->setCellValue('B1', 'תאריך ניפוק');
            $sheet->setCellValue('C1', 'מספר הזמנה');
            $sheet->setCellValue('D1', 'שם מחלקה');
            $sheet->setCellValue('E1', 'מספר אישי');
            $sheet->setCellValue('F1', 'שם מלא');
            $sheet->setCellValue('G1', 'סוג עובד');
            $sheet->setCellValue('H1', 'טלפון');
            $sheet->setCellValue('I1', 'מייל');
            $sheet->setCellValue('J1', 'כמות פר פריט');
            $sheet->setCellValue('K1', 'כמות סה"כ');
            $sheet->setCellValue('L1', 'סוג פריט');
            $sheet->setCellValue('M1', 'הערות על הפריט');
            $sheet->setCellValue('N1', 'הערות ראש מדור');
            $sheet->setCellValue('O1', 'הערות מנהל');
            $sheet->setCellValue('P1', 'הערות אפסנאי');
            $sheet->setCellValue('Q1', 'סטטוס');
            $sheet->setCellValue('R1', 'תאריך שינוי אחרון');
            $sheet->setCellValue('S1', 'מספר מק"ט');
            $sheet->setCellValue('T1', 'כמות פר מק"ט');

                        $row = 2;

            foreach ($distributions as $distribution) {
                // Add the main distribution data
                $sheet->setCellValue('A' . $row, $distribution->id ?? 'לא קיים');
                $sheet->setCellValue('B' . $row, $distribution->created_at_date ?? 'לא קיים');
                $sheet->setCellValue('C' . $row, $distribution->order_number ?? 'לא קיים');
                $sheet->setCellValue('D' . $row, $distribution->created_for ? $distribution->createdForUser->department->name  : 'לא קיים');
                $sheet->setCellValue('E' . $row, $distribution->created_for ? $distribution->createdForUser->personal_number : 'לא קיים');
                $sheet->setCellValue('F' . $row, $distribution->created_for ? $distribution->createdForUser->name : 'לא קיים');
                $sheet->setCellValue('G' . $row, $distribution->created_for ? $distribution->createdForUser->translated_employee_type : 'לא קיים');
                $sheet->setCellValue('H' . $row, $distribution->created_for ? $distribution->createdForUser->phone : 'לא קיים');
                $sheet->setCellValue('I' . $row, $distribution->created_for ? $distribution->createdForUser->email : 'לא קיים');
                $sheet->setCellValue('J' . $row, $distribution->quantity_per_item ?? 'לא קיים');
                $sheet->setCellValue('K' . $row, $distribution->total_quantity ?? 'לא קיים');
                $sheet->setCellValue('L' . $row, $distribution->type_id ? $distribution->itemType->type : 'לא קיים');
                $sheet->setCellValue('M' . $row, $distribution->type_comment ?? 'לא קיים');
                $sheet->setCellValue('N' . $row, $distribution->user_comment ?? 'לא קיים');
                $sheet->setCellValue('O' . $row, $distribution->admin_comment ?? 'לא קיים');
                $sheet->setCellValue('P' . $row, $distribution->quartermaster_comment ?? 'לא קיים');
                $sheet->setCellValue('Q' . $row, $distribution->getStatusTranslation() ?? 'לא קיים');
                $sheet->setCellValue('R' . $row, $distribution->updated_at_date ?? 'לא קיים');
                $sheet->setCellValue('S' . $row, $distribution->sku ?? 'לא קיים');
                $sheet->setCellValue('T' . $row, $distribution->quantity_per_inventory ??  'לא קיים');

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

            $sheet->getStyle('A1:T1')->applyFromArray($headerStyle);

            // Set & Style the cells
            $cellStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];

            // apply styling to all cells in the sheet
            $sheet->getStyle('A1:T' . ($row - 1))->applyFromArray($cellStyle);

            // set the size for rest of columns
            foreach (range('A', 'T') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // $filename = 'inventories.xlsx';
            $fileName = 'distributions_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            // Save the file to a temporary location
            $writer = new Xlsx($spreadsheet);

            $writer->save(storage_path('app/' .   $fileName));

            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"$fileName\"",
            ];

            return response()->download(storage_path('app/' .  $fileName), $fileName, $headers)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    //? search based on request->input('query').
    private function fetchDistributions(Request $request)
    {
        try {


            $query = Distribution::query();


 

        
            //? search by the associated id

            //? fetch by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }


            //? fetch by department
            //? fetch records only where created_for asscoiated with department_id
            if ($request->has('department_id') && empty($request->input('department_id')) == false) {

                // $query->where('department_id', $request->input('department_id'));
                $departmentId = $request->input('department_id');
                $query->whereHas('createdForUser', function ($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                });
            }


            // Search by order_number
            if ($request->has('order_number')) {
                $query->where('order_number', $request->input('order_number'));
            }
            // Search by user_id
            if ($request->has('clients_id') && empty($request->input('clients_id')) == false) {
                $query->whereIn('created_for', $request->input('clients_id'));
            }


            if ($request->has('created_at')) {
                $query->whereDate('created_at', $request->created_at);
            }

            if ($request->has('updated_at')) {
                $query->whereDate('updated_at', $request->updated_at);
            }

            // Ensure is_deleted is 0
            $query->where('is_deleted', 0);

            return $query->with(['itemType','createdForUser'])
            ->orderBy('created_at', 'desc')
            ->get();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);

    }


}