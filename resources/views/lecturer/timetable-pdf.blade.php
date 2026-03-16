<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Timetable' }}</title>
    <style>
        @page {
            margin: 0.3cm;
            size: A4 landscape;
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            margin: 0;
            color: #1e293b;
            line-height: 1.2;
        }
        .header {
            margin-bottom: 5px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 5px;
        }
        .header table {
            width: 100%;
            border: none;
        }
        .header td {
            border: none;
            vertical-align: middle;
        }
        .logo-txt {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
            letter-spacing: -1px;
        }
        .report-title {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }

        h2 { 
            color: #1e293b; 
            font-size: 16px;
            margin-top: 25px;
            margin-bottom: 10px;
            padding-left: 8px;
            border-left: 4px solid #4f46e5;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px;
            table-layout: fixed;
        }
        th, td { 
            border: 1px solid #e2e8f0; 
            padding: 10px; 
            text-align: left; 
            font-size: 11px;
            word-wrap: break-word;
        }
        th { 
            background-color: #4f46e5; 
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) {
            background-color: #f1f5f9;
        }
        .unit-code { font-weight: bold; color: #4f46e5; }
        .room-tag { 
            background-color: #ecfdf5; 
            color: #059669; 
            padding: 2px 6px; 
            border-radius: 4px; 
            font-weight: bold;
            border: 1px solid #d1fae5;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
        .no-classes { 
            text-align: center;
            padding: 40px;
            background: #f8fafc;
            border-radius: 12px;
            color: #64748b;
            font-style: italic;
            border: 2px dashed #e2e8f0;
        }
        .program-sub-header {
            background-color: #f1f5f9;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: bold;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #4f46e5;
            margin-top: 15px;
            margin-bottom: 0;
            text-transform: uppercase;
        }
        .master-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 10px;
        }
        .master-grid th, .master-grid td {
            border: 1px solid #e2e8f0;
            padding: 1px !important;
            font-size: 7px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td><div class="logo-txt">{{ strtoupper($user->institution->name ?? 'SMART SYSTEM') }}</div></td>
                <td><div class="report-title">{{ $title ?? 'Institution Wide Timetable' }}</div></td>
            </tr>
        </table>
    </div>



    @if(isset($isInstitutional) && $isInstitutional && isset($programChunks))
        @foreach($programChunks as $chunkIndex => $chunkData)
            @php 
                // Handle the new nested structure with Course context
                $currentPrograms = $chunkData['programs'] ?? $chunkData;
                $currentCourse = $chunkData['course'] ?? 'General';
            @endphp
            <div class="page-container" style="page-break-after: always; padding-bottom: 5px;">
                <table style="width: 100%; margin-bottom: 2px; border: none; border-bottom: 2px solid #4f46e5;">
                    <tr style="background: none;">
                        <td style="border: none; padding: 0; font-size: 10px; font-weight: bold; color: #4f46e5;">
                            {{ strtoupper($user->institution->name ?? 'SMART SYSTEM') }}
                        </td>
                        <td style="border: none; padding: 0; text-align: right; font-size: 7px; color: #64748b; font-weight: bold; text-transform: uppercase;">
                            {{ strtoupper($currentCourse) }} • Group {{ $chunkIndex + 1 }}
                        </td>
                    </tr>
                </table>
                
                <table class="master-grid" style="table-layout: fixed; width: 100%; border: 1.5px solid #334155;">
                    <thead>
                        <tr>
                            <th style="width: 15px; background-color: #f1f5f9; color: #475569; font-size: 6px; border-bottom: 2px solid #334155;">DAY</th>
                            <th style="width: 30px; background-color: #f1f5f9; color: #475569; font-size: 6px; border-bottom: 2px solid #334155;">TIME</th>
                            @foreach($currentPrograms as $key => $program)
                                <th style="background-color: #4f46e5; border-right: 1px solid #6366f1; border-bottom: 2px solid #1e1b4b; padding: 1px;">
                                    <div style="font-weight: 800; white-space: nowrap; overflow: hidden; font-size: 6px;">{{ $program['course'] }}</div>
                                    <div style="color: #c7d2fe; font-size: 5px; margin-top: 0px;">
                                        ({{ str_ireplace('Year ', 'Y', $program['year']) }})
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $dayNum => $dayName)
                            @if(!$loop->first)
                                <tr style="height: 1px; background-color: #334155;"><td colspan="{{ 2 + count($currentPrograms) }}" style="border: none; padding: 0 !important; height: 1px;"></td></tr>
                            @endif
                            @foreach($slots as $slotNum => $slotTime)
                                <tr>
                                    @if($loop->first)
                                        <td rowspan="{{ count($slots) }}" style="background-color: #f8fafc; font-weight: 900; text-align: center; font-size: 6px; width: 15px; border-right: 1.5px solid #334155; border-bottom: 1px solid #cbd5e1;">
                                            <div style="white-space: nowrap; color: #1e293b; font-size: 6px; letter-spacing: 0.5px;">{{ substr(strtoupper($dayName), 0, 3) }}</div>
                                        </td>
                                    @endif
                                    <td style="background-color: #fff; font-size: 5px; font-weight: bold; text-align: center; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; padding: 0.5px; color: #475569;">
                                        {{ $slotTime }}
                                    </td>
                                    @foreach($currentPrograms as $programKey => $program)
                                        @php $entry = $matrix[$dayNum][$slotNum][$programKey] ?? null; @endphp
                                        <td style="text-align: center; vertical-align: middle; height: 12px; padding: 0px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                                            @if($entry)
                                                <div style="font-weight: 900; color: #0f172a; font-size: 5.5px; margin-bottom: 0px; line-height: 0.9;">
                                                    {{ $entry->unit->code ?? '—' }}
                                                </div>
                                                <div style="color: #64748b; font-size: 4.5px; margin-bottom: 0px; line-height: 0.9; font-weight: 500;">
                                                    {{ $entry->lecturer->name ?? '—' }}
                                                </div>
                                                <div style="background-color: #f0fdf4; color: #166534; font-weight: 800; font-size: 4px; padding: 0.5px; border-radius: 1px; border: 0.5px solid #dcfce7; display: inline-block; line-height: 0.9; margin-top: 0.5px;">
                                                    {{ $entry->room->name ?? 'TBA' }}
                                                </div>
                                            @else
                                                <div style="color: #f8fafc; font-size: 2px;">.</div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @elseif(isset($isInstitutional) && $isInstitutional)
        <div class="no-classes">Rendering Error: Program chunks not found.</div>
    @else
        <!-- Lecturer Personal Timetable Grid -->
        <table style="width: 100%; margin-bottom: 5px; border: none; border-bottom: 2px solid #4f46e5; padding-bottom: 5px;">
            <tr style="background: none;">
                <td style="border: none; padding: 0; font-size: 14px; font-weight: bold; color: #4f46e5;">
                    {{ strtoupper($user->institution->name ?? 'SMART SYSTEM') }}
                </td>
                <td style="border: none; padding: 0; text-align: right; font-size: 14px; color: #64748b; font-weight: bold; text-transform: uppercase;">
                    MY TIMETABLE • {{ strtoupper($user->name) }}
                </td>
            </tr>
        </table>

        @if(count($entries) > 0)
            <table class="master-grid" style="table-layout: fixed; width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #e2e8f0; border-radius: 8px; margin-top: 15px; overflow: hidden;">
                <thead>
                    <tr>
                        <th style="width: 12%; background-color: #f8fafc; color: #64748b; font-size: 10px; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; text-align: left; padding: 12px 8px;">TIME SLOT</th>
                        @foreach($dayNames as $dayNum => $dayName)
                            <th style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; border-right: {{ $loop->last ? 'none' : '1px solid #e2e8f0' }}; padding: 12px 8px; text-align: left;">
                                <div style="font-weight: 800; color: #1e293b; font-size: 11px; text-transform: uppercase;">{{ $dayName }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeSlots as $slotNum => $slotLabel)
                        <tr>
                            <td style="background-color: #fcfcfc; font-weight: 700; text-align: left; border-right: 1px solid #e2e8f0; border-bottom: {{ $loop->last ? 'none' : '1px solid #e2e8f0' }}; padding: 10px 8px; color: #64748b; font-size: 9px;">
                                {{ $slotLabel }}
                            </td>
                            @foreach($dayNames as $dayNum => $dayName)
                                @php 
                                    $cellEntries = ($entriesByDay[$dayNum] ?? collect())->where('slot', $slotNum)->values(); 
                                @endphp
                                <td style="text-align: left; vertical-align: top; padding: 6px; border-right: {{ $loop->last ? 'none' : '1px solid #e2e8f0' }}; border-bottom: {{ $parentLoopLast = $loop->parent->last ? 'none' : '1px solid #e2e8f0' }}; background-color: {{ count($cellEntries) > 0 ? '#ffffff' : '#f8fafc' }};">
                                    @forelse($cellEntries as $entry)
                                        <div style="margin-bottom: 6px; padding: 6px; border-radius: 6px; background-color: #eef2ff; border-left: 3px solid #6366f1;">
                                            <div style="font-weight: 800; color: #1e3a8a; font-size: 9px; margin-bottom: 2px; line-height: 1.2;">
                                                {{ $entry->unit->code ?? '—' }}
                                            </div>
                                            <div style="color: #334155; font-size: 8px; margin-bottom: 4px; line-height: 1.2; font-weight: 600;">
                                                {{ $entry->unit->name ?? '—' }}
                                            </div>
                                            <div style="color: #64748b; font-size: 7px; margin-bottom: 4px; line-height: 1.2;">
                                                {{ $entry->course->name ?? '—' }} (Y{{ str_replace('Year ', '', $entry->year_of_study ?? '—') }})
                                            </div>
                                            <div style="background-color: #dcfce7; color: #166534; font-weight: 700; font-size: 7px; padding: 2px 4px; border-radius: 4px; display: inline-block;">
                                                {{ $entry->room->name ?? 'TBA' }}
                                            </div>
                                        </div>
                                    @empty
                                        <div style="color: #cbd5e1; font-size: 10px; text-align: center; padding-top: 15px;">—</div>
                                    @endforelse
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-classes" style="margin-top: 20px;">No sessions assigned for this week.</div>
        @endif
    @endif

    <div class="footer">
        © {{ date('Y') }} SMART University Timetabling System • Lecturer Specific Timetable • Confidential Report
    </div>
</body>
</html>
