@php
$column_number = $check_user_data_array['column_number'];

$index_1_column_dn = $column_number[0];
$index_1_column_given_name = $column_number[1];
$index_1_column_mail = $column_number[2];
$index_1_column_employee_number = $column_number[3];
$index_1_column_employee_type = $column_number[4];
@endphp

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
                    <!-- td style="width: 20%;text-align: right !important;"> DN </td -->
                </tr>
            </thead>
            <tbody>
                @foreach($check_user_data_array['array_user_ad'] as $key => $value)
                    <tr>
                        <td> {{ $value[$index_1_column_given_name] }} </td>
                        <td> {{ $value[$index_1_column_mail] }} </td>
                        <td> {{ $value[$index_1_column_employee_type] }} </td>
                        <td> {{ intval($value[$index_1_column_employee_number]) }} </td>
                        <!-- td> {{ $value[$index_1_column_dn] }} </td -->
                    </tr>
                @endforeach
            </tbody>
        </table>

    @endif

@endisset

<p>****** System Generated Message ******</p>