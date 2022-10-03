<!DOCTYPE html>
<html>
<head>
	<title>Report Project Docking</title>
	<link 
        rel="stylesheet" 
        href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" 
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" 
        crossorigin="anonymous"
    >
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
</head>
<body>
	<style type="text/css">
        @font-face {
            font-family: 'Open Sans';
            src: url({{ storage_path('fonts/open-sans/OpenSans-Regular.ttf') }}) format('truetype');
            font-weight: 400;
            font-style: normal;
        }
        body{
            font-family: "Open Sans";
            font-weight: 400;
            font-size: 7;
            line-height: 10;
        }
		table tr td,
		table tr th{
            padding:3px 5px !important;
            line-height: 12px;
            font-family: "Open Sans" !important;
		}
        .fa {
            display: inline;
            font-style: normal;
            font-variant: normal;
            font-weight: normal;
            font-size: 14px;
            line-height: 1;
            font-family: FontAwesome;
            font-size: inherit;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
	</style>

    <table style="width:100%">
        <tr>
            <td valign="top" style="width:50%">
                <table>
                    <tr>
                        <th width="70">Vessel</th>
                        <td>{{$proyek['kapal']['nama_kapal']}}</td>
                    </tr>
                    <tr>
                        <th width="70">Phase</th>
                        <td>{{$status}}</td>
                    </tr>
                    <tr>
                        <th width="70">Selected Yard</th>
                        <td>{{$tender['shipyard']['nama_lengkap']}}</td>
                    </tr>
                    <tr>
                        <th width="70">Base Currency</th>
                        <td>{{$proyek['mata_uang']}}</td>
                    </tr>
                    <tr>
                        <th width="70">Off Hire Period</th>
                        <td>{{$proyek['off_hire_start']}} &nbsp;<strong>-</strong>&nbsp; {{$proyek['off_hire_end']}} &nbsp; &nbsp; <strong>{{$proyek['off_hire_period']}}</strong> day</td>
                    </tr>
                    <tr>
                        <th width="70">- Deviation</th>
                        <td><strong>{{$proyek['off_hire_deviasi']}} day</strong></td>
                    </tr>
                    <tr>
                        <th width="70">- Rate</th>
                        <td>{{number_format($proyek['off_hire_rate_per_day'], 2)}}/day &nbsp; &nbsp; ={{number_format($proyek['off_hire_rate_per_day']*($proyek['off_hire_period']+$proyek['off_hire_deviasi']), 2)}}</td>
                    </tr>
                    <tr>
                        <th width="70">- Bunker</th>
                        <td>{{number_format($proyek['off_hire_bunker_per_day'], 2)}}/day &nbsp; &nbsp; ={{number_format($proyek['off_hire_bunker_per_day']*($proyek['off_hire_period']+$proyek['off_hire_deviasi']), 2)}}</td>
                    </tr>
                    <tr>
                        <th width="70">Repair Period</th>
                        <td>{{$proyek['repair_start']}} &nbsp;<strong>-</strong>&nbsp; {{$proyek['repair_end']}} &nbsp; &nbsp; <strong>{{$proyek['repair_period']}}</strong> day</td>
                    </tr>
                    <tr>
                        <th width="70">- In Dock</th>
                        <td>{{$proyek['repair_in_dock_start']}} &nbsp;<strong>-</strong>&nbsp; {{$proyek['repair_in_dock_end']}} &nbsp; &nbsp; <strong>{{$proyek['repair_in_dock_period']}}</strong> day</td>
                    </tr>
                    <tr>
                        <th width="70">- Additional Days</th>
                        <td><strong>{{$proyek['repair_additional_day']}}</strong> day</td>
                    </tr>
                </table>
            </td>
            <td valign="top" style="width:50%">
                <table class="table table-bordered">
                    <tr>
                        <th>Total</th>
                        <th>Budget</th>
                        <th>Kontrak</th>
                        <th>Aktual</th>
                    </tr>
                    <tr>
                        <th>Off Hire Day</th>
                        <td>{{$proyek['off_hire_period']}} day</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <th>Owner EXP</th>
                        <td align="right">{{number_format($summary_proyek['budget']['owner_exp'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['kontrak']['owner_exp'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['aktual']['owner_exp'], 2)}}</td>
                    </tr>
                    <tr>
                        <th>- Supplies</th>
                        <td align="right">{{number_format($summary_proyek['budget']['supplies'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['kontrak']['supplies'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['aktual']['supplies'], 2)}}</td>
                    </tr>
                    <tr>
                        <th>- Services</th>
                        <td align="right">{{number_format($summary_proyek['budget']['services'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['kontrak']['services'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['aktual']['services'], 2)}}</td>
                    </tr>
                    <tr>
                        <th>- Class</th>
                        <td align="right">{{number_format($summary_proyek['budget']['class'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['kontrak']['class'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['aktual']['class'], 2)}}</td>
                    </tr>
                    <tr>
                        <th>- Others</th>
                        <td align="right">{{number_format($summary_proyek['budget']['others'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['kontrak']['others'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['aktual']['others'], 2)}}</td>
                    </tr>
                    <tr>
                        <th>Yard Cost</th>
                        <td align="right">{{number_format($summary_proyek['budget']['yard_cost'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['kontrak']['yard_cost'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['aktual']['yard_cost'], 2)}}</td>
                    </tr>
                    <tr>
                        <th>Yard Canceled Jobs</th>
                        <td align="right">{{number_format($summary_proyek['budget']['yard_canceled_jobs'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['kontrak']['yard_canceled_jobs'], 2)}}</td>
                        <td align="right">{{number_format($summary_proyek['aktual']['yard_canceled_jobs'], 2)}}</td>
                    </tr>
                    <tr>
                        <th>Total Cost</th>
                        <td style="text-align: right"><strong>{{number_format($summary_proyek['budget']['total_cost'], 2)}}</strong></td>
                        <td style="text-align: right"><strong>{{number_format($summary_proyek['kontrak']['total_cost'], 2)}}</strong></td>
                        <td style="text-align: right"><strong>{{number_format($summary_proyek['aktual']['total_cost'], 2)}}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
 
	<table class='table' style="margin-top:100px">
		<thead>
			<tr>
				<th>SFI</th>
				<th>Pekerjaan</th>
				<th>%</th>
				<th>Resp</th>
				<th>Start</th>
				<th>End</th>
				<th>Duration</th>
				<th>Harga Kontrak</th>
				<th>Additional</th>
				<th>Harga Aktual</th>
				<th>Diff</th>
                <th></th>
			</tr>
		</thead>
		<tbody>
            @foreach($collapse_work_area as $c)
                @php
                    $diff=$c['total_harga_kontrak']-$c['total_harga_aktual_plus_additional'];
                @endphp

                @if($c['parent']==1 && $c['type']=="kategori")
                    <tr style="background:#ccc">
                @elseif($c['parent']==2 && $c['type']=="kategori")
                    <tr style="background:#dfdfdf">
                @elseif($c['parent']==3 && $c['type']=="kategori")
                    <tr style="background:#efefef">
                @elseif($c['parent']==4 && $c['type']=="kategori")
                    <tr style="background:#f6f6f6">
                @else
                    <tr style="background:#fff">
                @endif
                    <td>{{$c['sfi']}}</td>
                    <td>{{$c['pekerjaan']}}</td>
                    <td align="right">{{number_format($c['progress'], 2)}}</td>
                    <td>
                        @if($c['type']=="pekerjaan")
                            <span>{{$c['responsible']}}</span>
                        @endif
                    </td>
                    <td>{{$c['start']}}</td>
                    <td>{{$c['end']}}</td>
                    <td>{{count_day($c['start'], $c['end'])}} hari</td>
                    <td align="right">{{number_format($c['total_harga_kontrak'], 2)}}</td>
                    <td align="right">{{number_format($c['additional'], 2)}}</td>
                    <td align="right">{{number_format($c['total_harga_aktual'], 2)}}</td>
                    <td align="right">
                        @if($diff>0)
                            <span style="color:#098761">
                        @elseif($diff<0)
                            <span style="color:#bd0b14">
                        @else
                            <span>
                        @endif
                        {{number_format($diff, 2)}}</span>
                    </td>
                    <td>
                        @if($c['type']=="pekerjaan")
                            @if($c['approved_shipowner'])
                                <span class="fa fa-check" style="color:#098761"></span>
                            @else
                                <span class="fa fa-exclamation-circle" style="color:#ccbe1f"></span>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
            <tr style="background:#7a7a7a;color:#fff">
				<th colspan="2" align="right" style="text-align: right">TOTAL/SUMMARY</th>
				<th align="right" style="text-align: right">{{number_format($summary_work_area['progress'], 2)}}</th>
				<th></th>
				<th>{{$summary_work_area['start']}}</th>
				<th>{{$summary_work_area['end']}}</th>
				<th>{{count_day($summary_work_area['start'], $summary_work_area['end'])}}</th>
				<th align="right" style="text-align: right">{{number_format($summary_work_area['total_harga_kontrak'], 2)}}</th>
				<th align="right" style="text-align: right">{{number_format($summary_work_area['additional'], 2)}}</th>
				<th align="right" style="text-align: right">{{number_format($summary_work_area['total_harga_aktual'], 2)}}</th>
				<th align="right" style="text-align: right">{{number_format($summary_work_area['total_harga_kontrak']-$summary_work_area['total_harga_aktual_plus_additional'], 2)}}</th>
                <th></th>
			</tr>
		</tbody>
	</table>
</body>
</html>