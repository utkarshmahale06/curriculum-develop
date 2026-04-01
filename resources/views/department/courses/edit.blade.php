@extends('layouts.app')

@section('title', 'Design Courses')

@php
    $semesterOptions = ['I-Sem', 'II-Sem', 'III-Sem', 'IV', 'V', 'VI'];
    $requiredCourseCount = $basketOptions->sum('courses');
    $basketValidationOptions = $basketOptions->map(function ($basket) {
        return [
            'id' => $basket->id,
            'name' => $basket->basket_name,
            'course_type' => $basket->basket_name,
            'cl' => (int) ($basket->cl ?? 0),
            'tl' => (int) ($basket->tl ?? 0),
            'll' => (int) ($basket->ll ?? 0),
            'hours' => (int) $basket->hours,
            'credits' => (int) $basket->credits,
            'marks' => (int) $basket->marks,
        ];
    })->values()->all();
    $existingCourses = old('courses', $department->courses->map(function ($course) {
        return [
            'id' => $course->id,
            'course_basket_id' => $course->course_basket_id,
            'semester_name' => $course->semester_name,
            'sr_no' => $course->sr_no,
            'course_title' => $course->course_title,
            'abbreviation' => $course->abbreviation,
            'course_type' => $course->course_type,
            'total_iks_hours' => $course->total_iks_hours,
            'cl' => $course->cl,
            'tl' => $course->tl,
            'll' => $course->ll,
            'self_learning' => $course->self_learning,
            'credits' => $course->credits,
            'paper_duration' => $course->paper_duration,
            'fa_th_max' => $course->fa_th_max,
            'sa_th_max' => $course->sa_th_max,
            'theory_min' => $course->theory_min,
            'fa_pr_max' => $course->fa_pr_max,
            'fa_pr_min' => $course->fa_pr_min,
            'sa_pr_max' => $course->sa_pr_max,
            'sa_pr_min' => $course->sa_pr_min,
            'sla_max' => $course->sla_max,
            'sla_min' => $course->sla_min,
        ];
    })->toArray());
@endphp

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; margin-bottom: 22px;">
        <div>
            <h2 style="margin-bottom: 6px;">Design Courses</h2>
            <p style="color: #6b7280;">Programme: <strong>{{ $department->name }}</strong>. The table below follows the Excel course-structure format and now supports local browser autosave.</p>
        </div>
        <a href="{{ route('department.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px;">
        <div class="card" style="padding: 14px;">
            <div style="font-size: 12px; color: #6b7280;">Programme Code</div>
            <div style="font-size: 18px; font-weight: 600;">{{ $department->code }}</div>
        </div>
        <div class="card" style="padding: 14px;">
            <div style="font-size: 12px; color: #6b7280;">Academic Year</div>
            <div style="font-size: 18px; font-weight: 600;">{{ $department->year }}</div>
        </div>
        <div class="card" style="padding: 14px;">
            <div style="font-size: 12px; color: #6b7280;">Required Course Slots</div>
            <div style="font-size: 18px; font-weight: 600;">{{ $requiredCourseCount }}</div>
        </div>
        <div class="card" style="padding: 14px;">
            <div style="font-size: 12px; color: #6b7280;">Current CDC Status</div>
            <div style="font-size: 18px; font-weight: 600;">{{ $department->workflowLabel() }}</div>
        </div>
    </div>

    <div class="alert alert-warning">
        Match the required number of courses for each basket and keep the combined totals of CL, TL, LL, Hours, Credits, and Marks equal to the CDC-approved programme basket: {{ $basketOptions->map(fn ($basket) => $basket->basket_name . ' (' . $basket->courses . ' courses, total ' . ($basket->cl ?? 0) . '-' . ($basket->tl ?? 0) . '-' . ($basket->ll ?? 0) . ', ' . $basket->credits . ' credits, ' . $basket->marks . ' marks)')->implode(', ') }}.
    </div>

    <div class="alert alert-success">
        Total course slots for this scheme: <strong>{{ $requiredCourseCount }}</strong>. Designed rows cannot exceed this total.
    </div>

    <div class="alert alert-warning" id="liveValidationSummary" style="display: none;"></div>

    <div class="alert alert-success">
        You can use <strong>Save Draft</strong> to store partially completed course rows. Use <strong>Final Save</strong> only when the basket totals fully match the CDC scheme.
    </div>

    @if($department->cdc_review_status === 'revision_requested' && $department->cdc_review_remarks)
        <div class="alert alert-error">
            <strong>CDC Revision Note:</strong> {{ $department->cdc_review_remarks }}
        </div>
    @endif

    <div class="alert alert-warning">
        Large form tip: your row data is saved automatically in this browser while you work. Submitting the form clears the local autosave snapshot.
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('department.courses.update', $department) }}" id="courseDesignForm">
        @csrf

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; margin: 0;">Course Rows</h3>
            <button type="button" class="btn btn-primary" id="addCourseRow">+ Add Course</button>
        </div>

        <div class="course-table-shell">
            <table id="courseTable">
                <thead>
                    <tr>
                        <th>Basket</th>
                        <th>Semester</th>
                        <th>Sr No</th>
                        <th>Course Title</th>
                        <th>Abbrev.</th>
                        <th>Course Type</th>
                        <th>IKS</th>
                        <th>CL</th>
                        <th>TL</th>
                        <th>LL</th>
                        <th>Self Learning</th>
                        <th>Notional Hrs</th>
                        <th>Credits</th>
                        <th>Paper Duration</th>
                        <th>FA-TH Max</th>
                        <th>SA-TH Max</th>
                        <th>Theory Total</th>
                        <th>Theory Min</th>
                        <th>FA-PR Max</th>
                        <th>FA-PR Min</th>
                        <th>SA-PR Max</th>
                        <th>SA-PR Min</th>
                        <th>SLA Max</th>
                        <th>SLA Min</th>
                        <th>Total Marks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="courseTableBody"></tbody>
            </table>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-secondary" name="save_mode" value="draft">Save Draft</button>
            <button type="submit" class="btn btn-success" name="save_mode" value="final" id="finalSaveButton">Final Save</button>
            <a href="{{ route('department.dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
    #courseTable th,
    #courseTable td {
        white-space: nowrap;
        vertical-align: top;
    }
    .course-table-shell {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        max-height: 68vh;
    }
    #courseTable thead th {
        position: sticky;
        top: 0;
        z-index: 2;
    }
    #courseTable input,
    #courseTable select {
        width: 100%;
        min-width: 88px;
        padding: 8px 10px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        background: #fff;
    }
    #courseTable input[readonly] {
        background: #f8fafc;
        font-weight: 600;
        color: #334155;
    }
    .btn-danger {
        background: #ef4444;
        color: #fff;
        padding: 7px 14px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
    }
    .live-warning {
        margin-top: 8px;
        font-size: 12px;
        color: #b91c1c;
        line-height: 1.4;
        white-space: normal;
        min-width: 180px;
    }
    .field-warning {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.08);
    }
    @media (max-width: 900px) {
        .course-table-shell {
            max-height: none;
        }
    }
</style>

<script>
    const basketOptions = @json($basketValidationOptions);
    const semesterOptions = @json($semesterOptions);
    const existingCourses = @json($existingCourses);
    const requiredCourseCount = @json($requiredCourseCount);
    const autosaveKey = 'course-design-{{ $department->id }}';
    let rowIndex = 0;

    document.addEventListener('DOMContentLoaded', function () {
        const restoredCourses = resolveInitialCourses();

        if (restoredCourses.length > 0) {
            restoredCourses.forEach(function (course) {
                addRow(course);
            });
        } else {
            addRow();
        }

        document.getElementById('courseDesignForm').addEventListener('submit', function () {
            localStorage.removeItem(autosaveKey);
        });
    });

    document.getElementById('addCourseRow').addEventListener('click', function () {
        addRow();
    });

    function addRow(data = {}) {
        if (document.querySelectorAll('#courseTableBody tr').length >= requiredCourseCount) {
            alert(`You can design only ${requiredCourseCount} courses for this scheme.`);
            return;
        }

        const index = rowIndex++;
        const tbody = document.getElementById('courseTableBody');
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${hiddenInput(index, 'id', data.id ?? '')}${basketSelect(index, data.course_basket_id ?? '')}</td>
            <td>${semesterSelect(index, data.semester_name ?? '')}</td>
            <td>${numberInput(index, 'sr_no', data.sr_no ?? '', 1)}</td>
            <td>${textInput(index, 'course_title', data.course_title ?? '', 'Course title')}</td>
            <td>${textInput(index, 'abbreviation', data.abbreviation ?? '', 'PHY')}</td>
            <td>${textInput(index, 'course_type', data.course_type ?? '', 'Auto from basket', true)}</td>
            <td>${numberInput(index, 'total_iks_hours', data.total_iks_hours ?? '', 0)}</td>
            <td>${numberInput(index, 'cl', data.cl ?? '', 0, 'metric')}</td>
            <td>${numberInput(index, 'tl', data.tl ?? '', 0, 'metric')}</td>
            <td>${numberInput(index, 'll', data.ll ?? '', 0, 'metric')}</td>
            <td>${numberInput(index, 'self_learning', data.self_learning ?? '', 0, 'metric')}</td>
            <td><input type="number" class="notional-output" readonly value="0"></td>
            <td>${numberInput(index, 'credits', data.credits ?? '', 1)}</td>
            <td>${numberInput(index, 'paper_duration', data.paper_duration ?? '', 0, '', '0.5')}</td>
            <td>${numberInput(index, 'fa_th_max', data.fa_th_max ?? '', 0, 'assessment')}</td>
            <td>${numberInput(index, 'sa_th_max', data.sa_th_max ?? '', 0, 'assessment')}</td>
            <td><input type="number" class="theory-output" readonly value="0"></td>
            <td>${numberInput(index, 'theory_min', data.theory_min ?? '', 0)}</td>
            <td>${numberInput(index, 'fa_pr_max', data.fa_pr_max ?? '', 0, 'assessment')}</td>
            <td>${numberInput(index, 'fa_pr_min', data.fa_pr_min ?? '', 0)}</td>
            <td>${numberInput(index, 'sa_pr_max', data.sa_pr_max ?? '', 0, 'assessment')}</td>
            <td>${numberInput(index, 'sa_pr_min', data.sa_pr_min ?? '', 0)}</td>
            <td>${numberInput(index, 'sla_max', data.sla_max ?? '', 0, 'assessment')}</td>
            <td>${numberInput(index, 'sla_min', data.sla_min ?? '', 0)}</td>
            <td><input type="number" class="total-output" readonly value="0"></td>
            <td>
                <button type="button" class="btn-danger remove-row">Remove</button>
                <div class="live-warning"></div>
            </td>
        `;

        tbody.appendChild(row);

        const basketField = row.querySelector('[name$="[course_basket_id]"]');
        basketField.addEventListener('change', function () {
            syncCourseType(row);
            validateAllRows();
        });

        row.querySelectorAll('.metric').forEach(function (input) {
            input.addEventListener('input', function () {
                recalcRow(row);
                persistDraft();
            });
        });

        row.querySelectorAll('.assessment').forEach(function (input) {
            input.addEventListener('input', function () {
                recalcRow(row);
                persistDraft();
            });
        });

        row.querySelectorAll('input, select').forEach(function (input) {
            input.addEventListener('change', persistDraft);
            input.addEventListener('input', persistDraft);
        });

        row.querySelector('.remove-row').addEventListener('click', function () {
            row.remove();
            updateAddButtonState();
            validateAllRows();
            persistDraft();
        });

        syncCourseType(row);
        recalcRow(row);
        updateAddButtonState();
        validateAllRows();
    }

    function basketSelect(index, selected) {
        const options = basketOptions.map(function (option) {
            const isSelected = String(option.id) === String(selected) ? 'selected' : '';
            return `<option value="${option.id}" ${isSelected}>${escapeHtml(option.name)}</option>`;
        }).join('');

        return `<select name="courses[${index}][course_basket_id]"><option value="">Select</option>${options}</select>`;
    }

    function semesterSelect(index, selected) {
        const options = semesterOptions.map(function (option) {
            const isSelected = option === selected ? 'selected' : '';
            return `<option value="${option}" ${isSelected}>${option}</option>`;
        }).join('');

        return `<select name="courses[${index}][semester_name]"><option value="">Select</option>${options}</select>`;
    }

    function textInput(index, field, value, placeholder, readonly = false) {
        const readonlyAttribute = readonly ? 'readonly' : '';
        return `<input type="text" name="courses[${index}][${field}]" value="${escapeHtml(value)}" placeholder="${placeholder}" ${readonlyAttribute}>`;
    }

    function hiddenInput(index, field, value) {
        return `<input type="hidden" name="courses[${index}][${field}]" value="${escapeHtml(value)}">`;
    }

    function numberInput(index, field, value, min, cssClass = '', step = '1') {
        return `<input type="number" step="${step}" min="${min}" class="${cssClass}" name="courses[${index}][${field}]" value="${escapeHtml(value)}">`;
    }

    function recalcRow(row) {
        const cl = parseFloat(valueOrZero(row, '[name$="[cl]"]'));
        const tl = parseFloat(valueOrZero(row, '[name$="[tl]"]'));
        const ll = parseFloat(valueOrZero(row, '[name$="[ll]"]'));
        const selfLearning = parseFloat(valueOrZero(row, '[name$="[self_learning]"]'));
        const faTh = parseFloat(valueOrZero(row, '[name$="[fa_th_max]"]'));
        const saTh = parseFloat(valueOrZero(row, '[name$="[sa_th_max]"]'));
        const faPr = parseFloat(valueOrZero(row, '[name$="[fa_pr_max]"]'));
        const saPr = parseFloat(valueOrZero(row, '[name$="[sa_pr_max]"]'));
        const sla = parseFloat(valueOrZero(row, '[name$="[sla_max]"]'));

        row.querySelector('.notional-output').value = cl + tl + ll + selfLearning;
        row.querySelector('.theory-output').value = faTh + saTh;
        row.querySelector('.total-output').value = faTh + saTh + faPr + saPr + sla;
        validateAllRows();
    }

    function syncCourseType(row) {
        const basketId = parseInt(valueOrZero(row, '[name$="[course_basket_id]"]'));
        const basket = basketOptions.find(function (option) {
            return option.id === basketId;
        });
        const courseTypeInput = row.querySelector('[name$="[course_type]"]');

        courseTypeInput.value = basket ? basket.course_type : '';
    }

    function valueOrZero(row, selector) {
        const input = row.querySelector(selector);
        return input && input.value !== '' ? input.value : 0;
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function updateAddButtonState() {
        const addButton = document.getElementById('addCourseRow');
        const currentRows = document.querySelectorAll('#courseTableBody tr').length;
        const remaining = requiredCourseCount - currentRows;

        addButton.disabled = remaining <= 0;
        addButton.style.opacity = remaining <= 0 ? '0.6' : '1';
        addButton.style.cursor = remaining <= 0 ? 'not-allowed' : 'pointer';
        addButton.textContent = remaining > 0 ? `+ Add Course (${remaining} left)` : 'All Course Slots Used';
    }

    function validateAllRows() {
        const rows = Array.from(document.querySelectorAll('#courseTableBody tr'));
        const summaryBox = document.getElementById('liveValidationSummary');
        const summaryWarnings = [];

        rows.forEach(function (row) {
            row.querySelectorAll('input, select').forEach(function (field) {
                field.classList.remove('field-warning');
            });
            row.querySelector('.live-warning').textContent = '';
            row.dataset.invalid = '0';
        });

        basketOptions.forEach(function (basket) {
            const basketRows = rows.filter(function (row) {
                return parseInt(valueOrZero(row, '[name$="[course_basket_id]"]')) === basket.id;
            });

            if (basketRows.length === 0) {
                return;
            }

            const totals = basketRows.reduce(function (carry, row) {
                carry.cl += parseInt(valueOrZero(row, '[name$="[cl]"]')) || 0;
                carry.tl += parseInt(valueOrZero(row, '[name$="[tl]"]')) || 0;
                carry.ll += parseInt(valueOrZero(row, '[name$="[ll]"]')) || 0;
                carry.credits += parseInt(valueOrZero(row, '[name$="[credits]"]')) || 0;
                carry.marks += (parseInt(valueOrZero(row, '[name$="[fa_th_max]"]')) || 0)
                    + (parseInt(valueOrZero(row, '[name$="[sa_th_max]"]')) || 0)
                    + (parseInt(valueOrZero(row, '[name$="[fa_pr_max]"]')) || 0)
                    + (parseInt(valueOrZero(row, '[name$="[sa_pr_max]"]')) || 0)
                    + (parseInt(valueOrZero(row, '[name$="[sla_max]"]')) || 0);
                return carry;
            }, { cl: 0, tl: 0, ll: 0, credits: 0, marks: 0 });

            totals.hours = totals.cl + totals.tl + totals.ll;

            const warnings = [];

            if (totals.cl !== basket.cl) warnings.push(`total CL should be ${basket.cl}, currently ${totals.cl}`);
            if (totals.tl !== basket.tl) warnings.push(`total TL should be ${basket.tl}, currently ${totals.tl}`);
            if (totals.ll !== basket.ll) warnings.push(`total LL should be ${basket.ll}, currently ${totals.ll}`);
            if (totals.hours !== basket.hours) warnings.push(`total Hours should be ${basket.hours}, currently ${totals.hours}`);
            if (totals.credits !== basket.credits) warnings.push(`total Credits should be ${basket.credits}, currently ${totals.credits}`);
            if (totals.marks !== basket.marks) warnings.push(`total Marks should be ${basket.marks}, currently ${totals.marks}`);

            if (warnings.length > 0) {
                summaryWarnings.push(`${basket.name}: ${warnings.join('; ')}`);

                basketRows.forEach(function (row) {
                    row.dataset.invalid = '1';
                    row.querySelector('.live-warning').textContent = `${basket.name}: ${warnings.join('; ')}`;
                    row.querySelector('[name$="[cl]"]').classList.add('field-warning');
                    row.querySelector('[name$="[tl]"]').classList.add('field-warning');
                    row.querySelector('[name$="[ll]"]').classList.add('field-warning');
                    row.querySelector('[name$="[credits]"]').classList.add('field-warning');
                    row.querySelectorAll('.assessment').forEach(function (field) {
                        field.classList.add('field-warning');
                    });
                });
            }
        });

        summaryBox.style.display = summaryWarnings.length > 0 ? 'block' : 'none';
        summaryBox.innerHTML = summaryWarnings.join('<br>');
        updateSubmitState();
    }

    function updateSubmitState() {
        const submitButton = document.getElementById('finalSaveButton');
        const hasWarnings = Array.from(document.querySelectorAll('#courseTableBody tr')).some(function (row) {
            return row.dataset.invalid === '1';
        });

        submitButton.disabled = hasWarnings;
        submitButton.style.opacity = hasWarnings ? '0.6' : '1';
        submitButton.style.cursor = hasWarnings ? 'not-allowed' : 'pointer';
        submitButton.title = hasWarnings ? 'Resolve the row warnings before saving.' : '';
    }

    function resolveInitialCourses() {
        if (existingCourses.length > 0) {
            return existingCourses;
        }

        try {
            const stored = localStorage.getItem(autosaveKey);

            if (! stored) {
                return [];
            }

            const parsed = JSON.parse(stored);

            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function persistDraft() {
        const rows = Array.from(document.querySelectorAll('#courseTableBody tr')).map(function (row) {
            const rowData = {};

            row.querySelectorAll('input[name^="courses["], select[name^="courses["]').forEach(function (input) {
                const match = input.name.match(/\[([^\]]+)\]$/);

                if (match) {
                    rowData[match[1]] = input.value;
                }
            });

            return rowData;
        });

        localStorage.setItem(autosaveKey, JSON.stringify(rows));
    }
</script>
@endsection
