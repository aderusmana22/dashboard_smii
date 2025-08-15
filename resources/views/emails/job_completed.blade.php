<!DOCTYPE html>
<html>
<head>
    <title>Job Completed</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        .header {
            background-color: #a98f54;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .sub-header {
            background-color: #c0a76e;
            color: #ffffff;
            padding: 10px 20px;
        }
        .sub-header p {
            margin: 0;
            font-size: 18px;
            text-align: center; /* Ditambahkan untuk rata tengah */
        }
        .content {
            padding: 20px;
        }
        .content p {
            margin: 0 0 10px;
            line-height: 1.5;
        }
        .job-details {
            border-left: 4px solid #a98f54;
            padding-left: 15px;
            margin-top: 20px;
        }
        .job-details p {
            margin: 5px 0;
        }
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .history-table th, .history-table td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
            font-size: 12px; /* Smaller font size for the table */
            vertical-align: top;
        }
        .history-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #777777;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PT. Sinar Meadow International Indonesia</h1>
        </div>
        <div class="sub-header">
            <p>Marsho JobBoard</p>
        </div>
        <div class="content">
            {{-- Perubahan di sini: Menggunakan relasi marshoProfile untuk pengaju --}}
            <p>Dear {{ $job->pengaju->name }} ({{ optional(optional($job->pengaju->marshoProfile)->department)->department_name ?? 'N/A Marsho Dept.' }}),</p>
            <p>The job you submitted with ID <strong>{{ $job->id_job }}</strong> has been completed.</p>

            <div class="job-details">
                <p><strong>Job ID:</strong> {{ $job->id_job }}</p>
                <p><strong>Requester:</strong> {{ $job->pengaju->name }}</p>
                <p><strong>Location/Area:</strong> {{ $job->area->name }}</p>
                <p><strong>Description:</strong></p>
                <p>{{ $job->list_job }}</p>

                {{-- Displaying Initial Attachments --}}
                @if($job->initial_attachments->isNotEmpty())
                    <p style="margin-top: 20px;"><strong>Initial Attachments:</strong></p>
                    <ul>
                        @foreach($job->initial_attachments as $attachment)
                            <li><a href="{{ asset('storage/' . $attachment->file_path) }}">{{ $attachment->file_name }}</a></li>
                        @endforeach
                    </ul>
                @endif

                <p style="margin-top: 20px;"><strong>Job History:</strong></p>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>From</th>
                            <th>To</th>
                            <th>User</th>
                            <th>Note</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Loop through all job routes/steps --}}
                        @foreach($job->routes as $route)
                            <tr>
                                <td>
                                    @if($route->fromDepartment)
                                        {{ $route->fromDepartment->department_name }}
                                    @else
                                        Requester
                                    @endif
                                </td>
                                <td>{{ $route->toDepartment->department_name }}</td>
                                <td>{{ $route->creator->name }}</td>
                                <td>{{ $route->note }}</td>
                                <td>{{ $route->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                        @endforeach

                        {{-- Loop through completion notes --}}
                        @foreach($job->notes as $note)
                            <tr>
                                <td>{{ $job->latestRoute->toDepartment->department_name }}</td>
                                <td>Requester (Completed)</td>
                                <td>{{ $note->creator->name }}</td>
                                <td>{{ $note->note }}</td>
                                <td>{{ $note->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Displaying Completion Attachments --}}
                @if($job->closing_attachments->isNotEmpty())
                    <p style="margin-top: 20px;"><strong>Completion Attachments:</strong></p>
                    <ul>
                        @foreach($job->closing_attachments as $attachment)
                            <li><a href="{{ asset('storage/' . $attachment->file_path) }}">{{ $attachment->file_name }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} PT. Sinar Meadow International Indonesia. All rights reserved.</p>
        </div>
    </div>
</body>
</html>