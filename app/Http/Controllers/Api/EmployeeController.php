<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Employee;
use App\Models\JobTitle;

class EmployeeController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response 
    */
    protected function index()
    {
        return response()->json([
            'empleados' => Employee::all()
        ]);	
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\JsonResponse
    */
    protected function create()
    {
        return View::make('employees.create');
    }
    
    /**
     * store new employee
     *
     * @param  string $name, $lastname, $dni, $birth_date, $photo
     *
     * @return \Illuminate\Http\Response
     */
    protected function store(Request $request){
    	
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:120',
            'lastname' => 'required|max:120',
            'dni' => 'required|numeric|digits:11',
            'birth_date' => 'required',
            'photo' => 'required|mimes:jpeg,png|max:2000',
            'job' => 'required'
        ]);
            
        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'success' => false,
                'error' =>
                $validator->errors()->toArray()
            ], 400);
        }
        
        // create user for the employee
        $name = $request->input('name');
        $lastname = $request->input('lastname');
        $cont = 1;
        $email = Str::lower($name).substr(Str::lower($lastname), 0, $cont).'@test.com';

        while (User::where('email', $email)->exists()) {
            $cont ++;
            $email = Str::lower($name).substr(Str::lower($lastname), 0, $cont).'@test.com';
        }

        $user = User::create([
            'name' => $name,
            'lastname' => $lastname,
            'email' => $email,
            'password' => bcrypt($request->input('dni'))
        ]);
        
        // create the new employee
        $photo = $this->upload($request->file('photo'));
        $employee = Employee::create([
            'name' => $name,
            'lastname' => $lastname,
            'dni' => $request->input('dni'),
            'birth_date' => $request->input('birth_date'),
            'photo' => $photo,
            'user_id' => $user->id,
        ]);

        // save employee id in job_titles table
        $jobTitle = JobTitle::where('id', $request->input('job'))->first();
        
        $jobTitle->update([
            'employee_id' => $employee->id
        ]);
        
        return response()->json([
            'acción' => "Empleado agregado al sistema",
            'usuario' => $user,
            'empleado' => $employee
        ]);	
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    protected function show($id)
    {
        $employee = Employee::find($id);
        return response()->json([
            'empleado' => $employee
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    protected function edit($id)
    {
        $employee = Employee::find($id);
        
        return View::make('employees.edit')->with('employee', $employee);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|max:120',
            'lastname' => 'sometimes|max:120',
            'dni' => 'sometimes|numeric|digits:11',
            'birth_date' => 'sometimes',
            'photo' => 'sometimes|mimes:jpeg,png|max:2000',
            'job' => 'sometimes'
        ]);
            
        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'success' => false,
                'error' =>
                $validator->errors()->toArray()
            ], 400);
        }

        $name = $request->input('name');
        $lastname = $request->input('lastname');

        //updating the employee
        $employee = Employee::find($id);

        if ($employee == null) {
            return response()->json([
                'información' => "El usuario no existe en el sistema"
            ]);	
        }

        $employee->update(request()->all());

        //updating the user data
        $userId = $employee->user_id;
        $user = User::find($userId);

        if ($name != '' || $lastname != '') {
            $cont = 1;
            $email = Str::lower($employee->name).substr(Str::lower($employee->lastname), 0, $cont).'@test.com';

            while (User::where('email', $email)->exists()) {
                $cont ++;
                $email = Str::lower($employee->name).substr(Str::lower($employee->lastname), 0, $cont).'@test.com';
            }

            $user->update(['name' => $employee->name, 'lastname' => $employee->lastname, 'email' => $email]);
        }

        if ($request->input('dni') != '') {
            $user->update(['password' => bcrypt($request->input('dni'))]);
        }

        // update employee id in job_titles table
        if ($request->input('job') != '') {
            $jobTitle = JobTitle::where('id', $request->input('job'))->first();
            
            $jobTitle->update([
                'employee_id' => $employee->id
            ]);
        }

        return response()->json([
            'acción' => "Empleado modificado",
            'usuario' => $user,
            'empleado' => $employee
        ]);	
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * 
     */
    public function destroy($id)
    {
        $employee = Employee::where('id', $id)->first();
        $user = User::where('id', $employee->user_id)->first();
        $jobTitle = JobTitle::where('employee_id', $employee->id)->get();

        foreach ($jobTitle as $job) {
            $job->update(['employee_id' => null]);
        }
        
        $employee->delete();
        $user->delete();

        return response()->json([
            'acción' => "Empleado eliminado"
        ]);	
    }

    private function upload($image)
    {
        $pathInfo = pathinfo($image->getClientOriginalName());
        $employeePath = 'images/employee';

        $rename = uniqid() . '.' . $pathInfo['extension'];
        $image->move(public_path() . "/$employeePath", $rename);
        return "$employeePath/$rename";
    }
}