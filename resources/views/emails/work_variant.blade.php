Kepada <strong>{{$shipyard['nama_user']}}</strong>,<br/>
<strong>{{$shipyard['nama_perusahaan']}}</strong>
<p>Dengan Hormat,<br/>
Dalam rangka pengawasan dan pengendalian pelaksanaan pekerjaan Docking No. <strong>{{$no_docking}}</strong>, telah dilakukan perubahan daftar pekerjaan sebagai berikut;</p>

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
        <th>Category</th>
    </tr>
    @foreach ($work_variant as $wv)
        <tr>
            <td>{{$wv['job_no']}}</td>
            <td>{{$wv['job_name']}}</td>
            <td>{{$wv['progress']}}</td>
            <td>{{$wv['start']}}</td>
            <td>{{$wv['end']}}</td>
            <td>{{$wv['volume']}}</td>
            <td>{{$wv['unit']}}</td>
            <td>{{$wv['unit_price']}}</td>
            <td>{{$wv['total_price']}}</td>
            <td>{{$wv['category']}}</td>
        </tr>
    @endforeach
</table>
<p>Mohon melakukan perbaikan atau pembetulan apabila ditemukan ketidaksesuaian sebagaimana pelaksanaan di lapangan.</p>
<br/>
{{ date("d/m/Y H:i")}}<br/>
<strong>Admin SIKOMODO</strong>