@extends('layouts.app')

@section('title', 'Create Programme')

@section('content')
<div class="card">
    <h2>Create Programme</h2>

    {{-- Global validation errors --}}
    @if($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('cdc.departments.store') }}" id="programmeForm">
        @csrf

        {{-- Top Fields --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; margin-bottom: 28px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="name">Program Name <span style="color: #ef4444;">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Enter program name">
                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="code">Program Code <span style="color: #ef4444;">*</span></label>
                <input type="text" id="code" name="code" value="{{ old('code') }}" placeholder="Enter program code">
                @error('code')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="year">Year <span style="color: #ef4444;">*</span></label>
                <input type="number" id="year" name="year" value="{{ old('year') }}" placeholder="e.g. 2025" min="2000" max="2100">
                @error('year')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; margin-bottom: 28px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="award_class_subjects">No. of Award Class Subjects <span style="color: #ef4444;">*</span></label>
                <input type="number" id="award_class_subjects" name="award_class_subjects" value="{{ old('award_class_subjects') }}" placeholder="Enter number of award class subjects" min="0">
                @error('award_class_subjects')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Course Baskets --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="color: #111827; margin: 0; font-size: 16px; font-weight: 600;">Course Baskets</h3>
            <button type="button" class="btn btn-primary" id="addBasketBtn" style="font-size: 13px; padding: 8px 16px;">+ Add Course Basket</button>
        </div>

        <div style="overflow-x: auto; border-radius: 6px;">
            <table id="basketTable">
                <thead>
                    <tr>
                        <th>Basket Name</th>
                        <th>Courses</th>
                        <th>CL</th>
                        <th>TL</th>
                        <th>LL</th>
                        <th>Hours</th>
                        <th>Credits</th>
                        <th>Marks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="basketBody">
                    {{-- Rows added by JS --}}
                </tbody>
                <tfoot>
                    <tr style="font-weight: 700; background: #f1f5f9;">
                        <td colspan="5" style="text-align: right; color: #334155;">Grand Total</td>
                        <td id="grandHours" style="color: #111827;">0</td>
                        <td id="grandCredits" style="color: #111827;">0</td>
                        <td id="grandMarks" style="color: #111827;">0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-success">Save Programme</button>
            <a href="{{ route('cdc.departments.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<style>
    #basketTable input[type="text"],
    #basketTable input[type="number"] {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        color: #111827;
        background: #ffffff;
        box-sizing: border-box;
        transition: all 0.15s ease;
    }
    #basketTable input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    #basketTable input::placeholder {
        color: #9ca3af;
    }
    #basketTable td {
        padding: 10px 8px;
        vertical-align: top;
    }
    #basketTable input[readonly] {
        background: #f1f5f9;
        color: #334155;
        font-weight: 600;
    }
    .btn-danger {
        background: #ef4444;
        color: #fff;
        padding: 7px 14px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.15s ease;
    }
    .btn-danger:hover {
        background: #dc2626;
        box-shadow: 0 1px 2px rgba(239,68,68,0.2);
    }
</style>

<script>
    let rowIndex = 0;

    // Restore old basket data on validation failure
    const oldBaskets = @json(old('baskets', []));

    document.addEventListener('DOMContentLoaded', function () {
        if (oldBaskets && Object.keys(oldBaskets).length > 0) {
            Object.values(oldBaskets).forEach(function (basket) {
                addRow(basket);
            });
        } else {
            addRow();
        }
    });

    document.getElementById('addBasketBtn').addEventListener('click', function () {
        addRow();
    });

    function addRow(data) {
        const tbody = document.getElementById('basketBody');
        const idx = rowIndex++;
        const tr = document.createElement('tr');
        tr.setAttribute('data-row', idx);

        const basketName = data ? (data.basket_name || '') : '';
        const courses    = data ? (data.courses || '') : '';
        const cl         = data ? (data.cl || '') : '';
        const tl         = data ? (data.tl || '') : '';
        const ll         = data ? (data.ll || '') : '';
        const credits    = data ? (data.credits || '') : '';
        const marks      = data ? (data.marks || '') : '';

        tr.innerHTML = `
            <td><input type="text" name="baskets[${idx}][basket_name]" value="${escapeHtml(basketName)}" placeholder="Basket name"></td>
            <td style="width:90px"><input type="number" name="baskets[${idx}][courses]" value="${escapeHtml(courses)}" min="0" placeholder="0"></td>
            <td style="width:80px"><input type="number" name="baskets[${idx}][cl]" value="${escapeHtml(cl)}" min="0" class="calc-field" data-type="cl" placeholder="0"></td>
            <td style="width:80px"><input type="number" name="baskets[${idx}][tl]" value="${escapeHtml(tl)}" min="0" class="calc-field" data-type="tl" placeholder="0"></td>
            <td style="width:80px"><input type="number" name="baskets[${idx}][ll]" value="${escapeHtml(ll)}" min="0" class="calc-field" data-type="ll" placeholder="0"></td>
            <td style="width:80px"><input type="number" name="baskets[${idx}][hours]" readonly class="hours-field" value="0"></td>
            <td style="width:90px"><input type="number" name="baskets[${idx}][credits]" value="${escapeHtml(credits)}" min="0" placeholder="0"></td>
            <td style="width:90px"><input type="number" name="baskets[${idx}][marks]" value="${escapeHtml(marks)}" min="0" placeholder="0"></td>
            <td><button type="button" class="btn-danger remove-row">✕</button></td>
        `;

        tbody.appendChild(tr);

        // Attach event listeners
        tr.querySelectorAll('.calc-field').forEach(function (input) {
            input.addEventListener('input', function () {
                recalcRow(tr);
                recalcGrandTotal();
            });
        });

        // Credits and Marks also affect grand total
        tr.querySelector('[name$="[credits]"]').addEventListener('input', function () {
            recalcGrandTotal();
        });
        tr.querySelector('[name$="[marks]"]').addEventListener('input', function () {
            recalcGrandTotal();
        });

        tr.querySelector('.remove-row').addEventListener('click', function () {
            tr.remove();
            recalcGrandTotal();
        });

        // Initial calculation
        recalcRow(tr);
        recalcGrandTotal();
    }

    function recalcRow(tr) {
        const cl = parseInt(tr.querySelector('[data-type="cl"]').value) || 0;
        const tl = parseInt(tr.querySelector('[data-type="tl"]').value) || 0;
        const ll = parseInt(tr.querySelector('[data-type="ll"]').value) || 0;
        tr.querySelector('.hours-field').value = cl + tl + ll;
    }

    function recalcGrandTotal() {
        let totalHours = 0, totalCredits = 0, totalMarks = 0;

        document.querySelectorAll('#basketBody tr').forEach(function (tr) {
            totalHours   += parseInt(tr.querySelector('.hours-field').value) || 0;
            totalCredits += parseInt(tr.querySelector('[name$="[credits]"]').value) || 0;
            totalMarks   += parseInt(tr.querySelector('[name$="[marks]"]').value) || 0;
        });

        document.getElementById('grandHours').textContent   = totalHours;
        document.getElementById('grandCredits').textContent = totalCredits;
        document.getElementById('grandMarks').textContent   = totalMarks;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
</script>
@endsection
