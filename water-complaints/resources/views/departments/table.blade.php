@extends('layouts.app')

@section('content')
<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-bordered" id="departments-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departments as $department)
                    <tr>
                        <td>{{ $department->name }}</td>
                        <td style="width: 120px">
                            <div class='btn-group'>
                                <a href="{{ route('departments.show', [$department->id]) }}"
                                   class='btn btn-primary btn-sm'>
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('departments.edit', [$department->id]) }}"
                                   class='btn btn-warning btn-sm'>
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                {!! Form::open(['route' => ['departments.destroy', $department->id], 'method' => 'delete']) !!}
                                {!! Form::button('<i class="fas fa-trash-alt"></i> Delete', ['type' => 'submit', 'class' => 'btn btn-danger btn-sm', 'onclick' => "return confirm('Are you sure?')"]) !!}
                                {!! Form::close() !!}
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $departments])
        </div>
    </div>
</div>
@endsection