<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Department;
use App\Services\Department\DepartmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    //

    protected $_departmentService;

    public function __construct()
    {
        $this->_departmentService = new DepartmentService();
    }


    /**
     * Retrieve all departments.
     *
     * This endpoint retrieves all non-deleted departments.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/api/departments",
     *     summary="Retrieve all departments",
     *     tags={"Departments"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="משקים ומטה")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *         )
     *     )
     * )
     */

    public function index()
    {
        try {


            $result = $this->_departmentService->fetchDepartmentsRecords();

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['data'], Response::HTTP_OK),

                Status::INTERNAL_SERVER_ERROR => response()->json($result['message'], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };

        } catch (\Exception $e) {

            log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Store a newly created department.
     *
     * This endpoint creates a new department record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/departments",
     *     summary="Store a new department",
     *     tags={"Departments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="New Department Name")
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Department created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Department created successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="אחת מהשדות אינם תקינים")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        try {



            // Set custom error messages in Hebrew
            $customMessages = [
                'name.required' => 'שדה השם הוא חובה.',
                'name.string' => 'שדה ערך שם מחלקה אינו תקין.',
                'name.unique' => 'השם שהוזן כבר קיים במערכת.',
            ];

            // Set the rules
            $rules = [
                'name' => 'required|unique:departments,name,NULL,id,is_deleted,0',
            ];

            // Validate the request data
            $validator = Validator::make($request->all(), $rules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }



            $result = $this->_departmentService->store($request);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::CREATED => response()->json($result['message'], Response::HTTP_CREATED),

                Status::BAD_REQUEST => response()->json($result['message'], Response::HTTP_BAD_REQUEST),

                Status::UNPROCESSABLE_ENTITY => response()->json($result['message'], Response::HTTP_UNPROCESSABLE_ENTITY),

                Status::INTERNAL_SERVER_ERROR => response()->json($result['message'], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };


            // return response()->json(['message' => 'המחלקה נוצרה בהצלחה.'], Response::HTTP_CREATED);
        } catch (\Exception $e) {

            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחשה תקלה בשרת, נסה שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @OA\Delete(
     *      path="/api/departments/{id}",
     *      tags={"Departments"},
     *      summary="Delete an departments by ID",
     *      description="Deletes an departments based on the provided ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the departments to delete",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="שורה נמחקה בהצלחה.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="יש לשלוח מספר מזהה של שורה")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="שורה אינה קיימת במערכת.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="התרחש בעיית שרת יש לנסות שוב מאוחר יותר.")
     *          )
     *      )
     * )
     */

    public function destroy($id = null)
    {

        try {

            $result = $this->_departmentService->destroy($id);

            // Use match to handle different status cases
            return match ($result['status']) {

                Status::OK => response()->json($result['message'], Response::HTTP_OK),

                Status::BAD_REQUEST => response()->json($result['message'], Response::HTTP_BAD_REQUEST),

                Status::INTERNAL_SERVER_ERROR => response()->json($result['message'], Response::HTTP_INTERNAL_SERVER_ERROR),

                default => response()->json(['message' => 'Unknown error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR),
            };



        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }



}