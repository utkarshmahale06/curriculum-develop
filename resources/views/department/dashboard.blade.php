@extends('layouts.app')

@section('title', 'Department Dashboard')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 22px;">
        <div>
            <h2 style="margin-bottom: 6px;">Department Dashboard</h2>
            <p style="color: #6b7280;">Welcome, {{ Auth::user()->name }}. Design courses for the programmes assigned to you and track CDC review status.</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-bottom: 22px;">
        <div class="card" style="padding: 14px;">
            <div style="font-size: 12px; color: #6b7280;">Total</div>
            <div style="font-size: 20px; font-weight: 600;">{{ $summary['total'] }}</div>
        </div>
        <div class="card" style="padding: 14px;">
            <div style="font-size: 12px; color: #6b7280;">Draft</div>
            <div style="font-size: 20px; font-weight: 600;">{{ $summary['draft'] }}</div>
        </div>
        <div class="card" style="padding: 14px;">
            <div style="font-size: 12px; color: #6b7280;">Submitted</div>
            <div style="font-size: 20px; font-weight: 600;">{{ $summary['submitted'] }}</div>
        </div>
        <div class="card" style="padding: 14px;">
            <div style="font-size: 12px; color: #6b7280;">Revision</div>
            <div style="font-size: 20px; font-weight: 600;">{{ $summary['revision_requested'] }}</div>
        </div>
        <div class="card" style="padding: 14px;">
            <div style="font-size: 12px; color: #6b7280;">Approved</div>
            <div style="font-size: 20px; font-weight: 600;">{{ $summary['approved'] }}</div>
        </div>
        <div class="card" style="padding: 14px;">
            <div style="font-size: 12px; color: #6b7280;">Codes Assigned</div>
            <div style="font-size: 20px; font-weight: 600;">{{ $summary['codes_assigned'] }}</div>
        </div>
    </div>

    @if($assignedDepartments->isEmpty())
        <div class="alert alert-warning">
            No programme is assigned to your account yet. Ask CDC to assign one from the programme list.
        </div>
    @else
        <div style="display: grid; gap: 18px;">
            @foreach($assignedDepartments as $department)
                @php
                    $requiredCourses = $department->courseBaskets->sum('courses');
                    $designedCourses = $department->courses->count();
                    $progress = $requiredCourses > 0 ? min(100, (int) round(($designedCourses / $requiredCourses) * 100)) : 0;
                    $isReadyToSubmit = $designedCourses === $requiredCourses && $requiredCourses > 0;
                    $isSubmittedToCdc = $department->hasSubmittedCoursesToCdc();
                    $areCourseCodesAssigned = $department->hasAssignedCourseCodes();

                    if ($areCourseCodesAssigned) {
                        $workflowLabel = 'CDC codes assigned';
                        $workflowColor = '#166534';
                        $workflowBackground = '#f0fdf4';
                    } elseif ($department->cdc_review_status === 'revision_requested') {
                        $workflowLabel = 'Revision requested';
                        $workflowColor = '#991b1b';
                        $workflowBackground = '#fef2f2';
                    } elseif ($isSubmittedToCdc) {
                        $workflowLabel = 'Awaiting CDC review';
                        $workflowColor = '#92400e';
                        $workflowBackground = '#fffbeb';
                    } elseif ($department->cdc_review_status === 'approved') {
                        $workflowLabel = 'Approved by CDC';
                        $workflowColor = '#166534';
                        $workflowBackground = '#ecfdf5';
                    } elseif ($isReadyToSubmit) {
                        $workflowLabel = 'Ready for CDC';
                        $workflowColor = '#1d4ed8';
                        $workflowBackground = '#eff6ff';
                    } else {
                        $workflowLabel = 'Draft in progress';
                        $workflowColor = '#475569';
                        $workflowBackground = '#f1f5f9';
                    }
                @endphp
                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
                        <div>
                            <h3 style="font-size: 18px; margin-bottom: 6px;">{{ $department->name }}</h3>
                            <p style="color: #6b7280; margin-bottom: 12px;">Code: {{ $department->code }} | Year: {{ $department->year }}</p>
                            <p style="margin-bottom: 12px;">
                                <span style="display: inline-flex; align-items: center; padding: 6px 10px; border-radius: 999px; background: {{ $workflowBackground }}; color: {{ $workflowColor }}; font-size: 13px; font-weight: 600;">
                                    {{ $workflowLabel }}
                                </span>
                            </p>
                            <p style="color: #374151; margin-bottom: 10px;">Progress: <strong>{{ $designedCourses }}/{{ $requiredCourses }}</strong> courses designed ({{ $progress }}%)</p>
                            <div style="width: 100%; max-width: 380px; background: #e5e7eb; border-radius: 999px; height: 10px; overflow: hidden; margin-bottom: 12px;">
                                <div style="width: {{ $progress }}%; background: {{ $progress === 100 ? '#16a34a' : '#2563eb' }}; height: 100%;"></div>
                            </div>
                            @if($department->cdc_review_status === 'revision_requested')
                                <p style="color: #991b1b; font-size: 13px; margin-bottom: 12px;">
                                    CDC requested revisions{{ $department->cdc_review_remarks ? ': ' . $department->cdc_review_remarks : '.' }}
                                </p>
                            @elseif($isSubmittedToCdc && ! $areCourseCodesAssigned && $department->cdc_review_status !== 'approved')
                                <p style="color: #92400e; font-size: 13px; margin-bottom: 12px;">
                                    Submitted to CDC on {{ $department->courses_submitted_to_cdc_at?->format('d M Y, h:i A') }}. Waiting for CDC review.
                                </p>
                            @elseif($department->cdc_review_status === 'approved')
                                <p style="color: #166534; font-size: 13px; margin-bottom: 12px;">
                                    CDC approved the course design. Final course codes will appear after allocation.
                                </p>
                            @elseif($areCourseCodesAssigned)
                                <p style="color: #166534; font-size: 13px; margin-bottom: 12px;">
                                    CDC allocated course codes on {{ $department->course_codes_assigned_at?->format('d M Y, h:i A') }}.
                                </p>
                            @elseif($isReadyToSubmit)
                                <p style="color: #1d4ed8; font-size: 13px; margin-bottom: 12px;">
                                    All required courses are designed. You can now send them to CDC for course-code allocation.
                                </p>
                            @endif
                            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                @foreach($department->courseBaskets as $basket)
                                    @php
                                        $basketDesigned = $department->courses->where('course_basket_id', $basket->id)->count();
                                    @endphp
                                    <span style="padding: 6px 10px; border-radius: 999px; background: #eff6ff; color: #1d4ed8; font-size: 13px;">
                                        {{ $basket->basket_name }}: {{ $basketDesigned }}/{{ $basket->courses }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <a href="{{ route('department.courses.show', $department) }}" class="btn btn-secondary">View Designed Courses</a>
                            <a href="{{ route('department.courses.edit', $department) }}" class="btn btn-primary">
                                {{ $designedCourses < $requiredCourses ? 'Design Remaining Courses' : 'Design Courses' }}
                            </a>
                            @if($areCourseCodesAssigned)
                                <button type="button" class="btn btn-secondary" disabled>
                                    Codes Assigned by CDC
                                </button>
                            @elseif($isSubmittedToCdc)
                                <button type="button" class="btn btn-secondary" disabled>
                                    {{ $department->cdc_review_status === 'approved' ? 'Approved by CDC' : ($department->cdc_review_status === 'revision_requested' ? 'Revision Requested' : 'Under CDC Review') }}
                                </button>
                            @elseif($isReadyToSubmit)
                                <form action="{{ route('department.courses.submit', $department) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success" style="width: 100%;">Submit For CDC Review</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
