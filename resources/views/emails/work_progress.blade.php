Kepada <strong>{{$shipyard['nama_user']}}</strong>,<br/>
<strong>{{$shipyard['nama_perusahaan']}}</strong>
<p>Dengan Hormat,<br/>
Dalam rangka pengawasan dan pengendalian pelaksanaan pekerjaan Docking No. <strong>{{$no_docking}}</strong>, telah dilakukan perubahan pada kemajuan pekerjaan sebagai berikut;</p>

<table border="1" cellpadding="5" style="border-collapse: collapse">
    <tr>
        <th>Job No</th>
        <th>Job Name</th>
        <th>Progress</th>
        <th>Start</th>
        <th>End</th>
        <th>Volume</th>
        <th>Unit</th>
        <th>Unit Price</th>
        <th>Total Price</th>
    </tr>
    @foreach ($work_progress as $wp)
        <tr>
            <td>{{$wp['job_no']}}</td>
            <td>{{$wp['job_name']}}</td>
            <td>{{$wp['progress']}}</td>
            <td>{{$wp['start']}}</td>
            <td>{{$wp['end']}}</td>
            <td>{{$wp['volume']}}</td>
            <td>{{$wp['unit']}}</td>
            <td>{{$wp['unit_price']}}</td>
            <td>{{$wp['total_price']}}</td>
        </tr>
    @endforeach
</table>
<br/><br/>
{{ date("d/m/Y H:i")}}<br/>
<strong>Admin SIKOMODO</strong>