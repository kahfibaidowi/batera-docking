Kepada <strong>{{$shipyard['nama_user']}}</strong>,<br/>
<strong>{{$shipyard['nama_perusahaan']}}</strong>
<p>Dengan Hormat,<br/>
Memperhatikan pelaksanaan pengawasan dan pengendalian pekerjaan Docking No. <strong>{{$no_docking}}</strong>, telah dibuat berita acara pekerjaan sebagai berikut;</p>

<table border="1" cellpadding="5" style="border-collapse: collapse">
    <tr>
        <th>Title</th>
        <th>Sender</th>
        <th>Date</th>
        <th>Remarks</th>
    </tr>
    @foreach ($surat_teguran as $st)
        <tr>
            <td>{{$st['title']}}</td>
            <td>{{$st['sender']}}</td>
            <td>{{$st['date']}}</td>
            <td>{{$st['remarks']}}</td>
        </tr>
    @endforeach
</table>
<p>Mohon melakukan perbaikan atau pembetulan perihal ketidaksesuaian sebagaimana pelaksanaan di lapangan.</p>
<br/>
{{ date("d/m/Y H:i")}}<br/>
<strong>Admin SIKOMODO</strong>