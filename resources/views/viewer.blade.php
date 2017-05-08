<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>EE Acceptance Test Results Viewer</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref">
            <div class="content">
                <h1 class="title m-b-md">
                    EE Acceptance Test Viewer
                </h1>

                <h2>{!! $title !!}</h2>
                <p><strong>Commit Message:</strong> {{ $description }}</p>
                @if (empty($artifacts))
                    <div class="error">There are no artifacts for this build.</div>
                @else
                    @if (count($jobs_list) <= 1)
                        @foreach ($artifacts as $artifact_items)
                            @foreach ($artifact_items as $artifact)
                                <div>{!! $artifact !!}</div>
                            @endforeach
                        @endforeach
                    @else
                        @foreach ($jobs_list as $job_number => $job_link)
                            <h3><a href="{!! $job_link !!}">Job: {{ $job_number }}</a></h3>
                            @if (isset($artifacts[$job_number]))
                                @foreach($artifacts[$job_number] as $artifact)
                                    <div>{!! $artifact !!}</div>
                                @endforeach
                            @else
                                <p>There are no artifacts for this job.</p>
                            @endif
                        @endforeach
                    @endif
                @endif
            </div>
        </div>
    </body>
</html>
