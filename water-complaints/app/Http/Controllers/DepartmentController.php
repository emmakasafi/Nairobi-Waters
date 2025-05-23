<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\DepartmentRepository;
use Illuminate\Http\Request;
use Flash;

class DepartmentController extends AppBaseController
{
    /** @var DepartmentRepository $departmentRepository*/
    private $departmentRepository;

    public function __construct(DepartmentRepository $departmentRepo)
    {
        $this->departmentRepository = $departmentRepo;
    }

    /**
     * Display a listing of the Department.
     */
    public function index(Request $request)
    {
        $departments = $this->departmentRepository->paginate(10);

        return view('departments.index')
            ->with('departments', $departments);
    }

    /**
     * Show the form for creating a new Department.
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created Department in storage.
     */
    public function store(CreateDepartmentRequest $request)
    {
        $input = $request->all();

        $department = $this->departmentRepository->create($input);

        Flash::success('Department saved successfully.');

        return redirect(route('departments.index'));
    }

    /**
     * Display the specified Department.
     */
    /**
 * Display the specified Department.
 */
public function show($id)
{
    $department = $this->departmentRepository->find($id);

    if (empty($department)) {
        Flash::error('Department not found');
        return redirect(route('departments.index'));
    }

    // Retrieve all users for the department
    $users = $department->users;

    return view('departments.show', [
        'department' => $department,
        'users' => $users
    ]);
}

    /**
     * Show the form for editing the specified Department.
     */
    public function edit($id)
{
    $department = $this->departmentRepository->find($id);

    if (empty($department)) {
        Flash::error('Department not found');
        return redirect(route('departments.index'));
    }

    // Retrieve all users for the department
    $users = $department->users;

    return view('departments.edit', [
        'department' => $department,
        'users' => $users
    ]);
}

    /**
     * Update the specified Department in storage.
     */
    public function update($id, UpdateDepartmentRequest $request)
    {
        $department = $this->departmentRepository->find($id);

        if (empty($department)) {
            Flash::error('Department not found');

            return redirect(route('departments.index'));
        }

        $department = $this->departmentRepository->update($request->all(), $id);

        Flash::success('Department updated successfully.');

        return redirect(route('departments.index'));
    }

    /**
     * Remove the specified Department from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $department = $this->departmentRepository->find($id);

        if (empty($department)) {
            Flash::error('Department not found');

            return redirect(route('departments.index'));
        }

        $this->departmentRepository->delete($id);

        Flash::success('Department deleted successfully.');

        return redirect(route('departments.index'));
    }
}
