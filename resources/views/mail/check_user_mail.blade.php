@isset($user_array)
    
    <h3> Detected AD Users </h3>
    <!-- style="border: 1px solid black;" -->
    <table style="width: 100%;">
        <thead>
            <tr style="">
                <td style="width: 20%;text-align: right !important;"> DN </td>
                <td style="width: 20%;text-align: right !important;"> GIVEN NAME </td>
                <td style="width: 20%;text-align: right !important;"> MAIL </td>
                <td style="width: 20%;text-align: right !important;"> EMPLOYEE TYPE </td>
                <td style="width: 20%;text-align: right !important;"> EMPLOYEE NUMBER </td>
            </tr>
        </thead>
        <tbody>
            @foreach($user_array as $key => $value)
                <tr>
                    <td> {{ $value[0] }} </td>
                    <td> {{ $value[1] }} </td>
                    <td> {{ $value[2] }} </td>
                    <td> {{ $value[3] }} </td>
                    <td> {{ intval($value[4]) }} </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endisset

<p>****** System Genarated Message ******</p>