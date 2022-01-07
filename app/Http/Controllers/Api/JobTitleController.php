<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\JobTitle;

class JobTitleController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response 
    */
    protected function index()
    {
        return response()->json([
            'puestos laborales' => JobTitle::all()
        ]);	
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\JsonResponse
    */
    protected function create()
    {
        return View::make('jobs.create');
    }
    
    /**
     * store new job title
     *
     * @param  string $name, $code, $importance, $boss
     *
     * @return \Illuminate\Http\Response
     */
    protected function store(Request $request){
    	
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:120',
            'code' => 'required|numeric',
            'importance' => 'required',
            'boss' => 'required',
        ]);
            
        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'success' => false,
                'error' =>
                $validator->errors()->toArray()
            ], 400);
        }
        
        // create new job
        $jobTitle = JobTitle::create([
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'importance' => $request->input('importance'),
            'boss' => $request->input('boss')
        ]);
        
        return response()->json([
            'acción' => "Puesto laboral agregado al sistema",
            'puesto laboral' => $jobTitle
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
        $jobTitle = JobTitle::find($id);
        return response()->json([
            'job_title' => $jobTitle
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
        $jobTitle = JobTitle::find($id);
        
        return View::make('jobTitles.edit')->with('jobTitle', $jobTitle);
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
            'code' => 'sometimes|numeric',
            'importance' => 'sometimes',
            'boss' => 'sometimes'
        ]);
            
        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'success' => false,
                'error' =>
                $validator->errors()->toArray()
            ], 400);
        }

        //updating the job title
        $jobTitle = JobTitle::find($id);

        $jobTitle->update(request()->all());

        return response()->json([
            'acción' => "Puesto laboral modificado",
            'puesto laboral' => $jobTitle->name
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
        $jobTitle = JobTitle::where('id', $id)->first();

        if ($jobTitle->employee_id == null) {
            $jobTitle->delete();

            return response()->json([
                'acción' => "Puesto eliminado"
            ]);
        }

        return response()->json([
            'acción' => "El puesto tiene asociado un empleado y no se puede eliminar"
        ]);

    }

}