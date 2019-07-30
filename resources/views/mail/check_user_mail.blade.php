@isset($check_user_data_array)
    
    @if( (isset($check_user_data_array['message_title'])) && (!empty($check_user_data_array['message_title'])) )
        <h3> {{ $check_user_data_array['message_title'] }} </h3>
    @endif

    @if( (isset($check_user_data_array['message_body'])) && (!empty($check_user_data_array['message_body'])) )
        <!-- br/ -->
        <p> {{ $check_user_data_array['message_body'] }} </p>
    @endif

    @if( (isset($check_user_data_array['message_type'])) && (!empty($check_user_data_array['message_type'])) )
        @if( (strcasecmp($check_user_data_array['message_type'], 'error') == 0) )
            <!-- br/ -->
            <p>AD backup date : {{ $check_user_data_array['date_last_modified_ad']->format('Y-m-d') }}</p>
            <p>HCM backup date : {{ $check_user_data_array['date_last_modified_hcm']->format('Y-m-d') }}</p>
        @endif
    @endif

    @if( (isset($check_user_data_array['array_user_ad'])) && (!empty($check_user_data_array['array_user_ad'])) )

        <!-- style="border: 1px solid black;" -->
        <table style="width: 100%;">
            <thead>
                <tr style="">
                    <td style="width: 20%;text-align: right !important;"> GIVEN NAME </td>
                    <td style="width: 20%;text-align: right !important;"> MAIL </td>
                    <td style="width: 20%;text-align: right !important;"> EMPLOYEE TYPE </td>
                    <td style="width: 20%;text-align: right !important;"> EMPLOYEE NUMBER </td>
                </tr>
            </thead>
            <tbody>
                @foreach($check_user_data_array['array_user_ad'] as $key => $value)
                    <tr>
                        <td> {{ $value[1] }} </td>
                        <td> {{ $value[2] }} </td>
                        <td> {{ $value[3] }} </td>
                        <td> {{ intval($value[4]) }} </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @endif

@endisset

<p>****** System Generated Message ******</p>