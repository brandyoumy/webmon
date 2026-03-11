<h2>Website Monitoring Alert</h2>

<p>Displaying Website Status Changes As Follows :</p>
    @php
    $statusMeaning = [
        0 => 'No HTTP code was received',
        200 => 'OK – The request has succeeded',
        301 => 'Moved Permanently – URL has changed',
        302 => 'Found – Temporary redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
    ];
@endphp

<table style="width:100%; border-collapse: collapse; font-family: Arial, sans-serif;">
    <thead>
        <tr>
            <th style="border: 1px solid #ddd; padding: 8px; text-align:left;">Name</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align:left;">URL</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align:center;">Status</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align:center;">Status Code</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align:center;">Status Code Meaning</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align:center;">SSL</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($downSites as $item)
        <tr>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ $item['site']->name }}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ $item['site']->url }}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align:center;">
                <span style="
                    display:inline-block;
                    padding: 4px 8px;
                    border-radius: 12px;
                    color: #fff;
                    font-weight: bold;
                    background-color: {{ $item['isUp'] ? '#28a745' : '#dc3545' }};
                    ">
                    {{ $item['isUp'] ? 'Up' : 'Down' }}
                </span>
            </td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align:center;">{{ $item['statusCode'] }}</td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align:center;">
                {{ $statusMeaning[$item['statusCode']] ?? 'Unknown Status' }}
            </td>
            <td style="border: 1px solid #ddd; padding: 8px; text-align:center;">
                <span style="
                    display:inline-block;
                    padding: 4px 8px;
                    border-radius: 12px;
                    color: #fff;
                    font-weight: bold;
                    background-color: {{ $item['sslValid'] ? '#28a745' : '#dc3545' }};
                    ">
                    {{ $item['sslValid'] ? 'Valid' : 'Invalid' }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Reference Table for Status Codes -->
{{-- <h4 style="margin-top: 20px;">HTTP Status Code Reference</h4>
<table style="width:50%; border-collapse: collapse; font-family: Arial, sans-serif;">
    <thead>
        <tr>
            <th style="border: 1px solid #ddd; padding: 6px; text-align:center;">Code</th>
            <th style="border: 1px solid #ddd; padding: 6px; text-align:left;">Meaning</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="border: 1px solid #ddd; padding: 6px; text-align:center;">200</td>
            <td style="border: 1px solid #ddd; padding: 6px;">OK – The request has succeeded</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 6px; text-align:center;">301</td>
            <td style="border: 1px solid #ddd; padding: 6px;">Moved Permanently – URL has changed</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 6px; text-align:center;">302</td>
            <td style="border: 1px solid #ddd; padding: 6px;">Found – Temporary redirect</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 6px; text-align:center;">400</td>
            <td style="border: 1px solid #ddd; padding: 6px;">Bad Request</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 6px; text-align:center;">401</td>
            <td style="border: 1px solid #ddd; padding: 6px;">Unauthorized</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 6px; text-align:center;">403</td>
            <td style="border: 1px solid #ddd; padding: 6px;">Forbidden</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 6px; text-align:center;">404</td>
            <td style="border: 1px solid #ddd; padding: 6px;">Not Found</td>
        </tr>
        <tr>
            <td style="border: 1px solid #ddd; padding: 6px; text-align:center;">500</td>
            <td style="border: 1px solid #ddd; padding: 6px;">Internal Server Error</td>
        </tr>
    </tbody>
</table> --}}