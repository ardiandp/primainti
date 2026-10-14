<p>Ada pesan kontak baru dari website:</p>

<ul>
    <li><strong>Nama:</strong> {{ $contactMessage->name }}</li>
    <li><strong>Email:</strong> {{ $contactMessage->email }}</li>
    @if ($contactMessage->company)
        <li><strong>Perusahaan:</strong> {{ $contactMessage->company }}</li>
    @endif
    @if ($contactMessage->phone)
        <li><strong>Telepon:</strong> {{ $contactMessage->phone }}</li>
    @endif
    <li><strong>Subjek:</strong> {{ $contactMessage->subject }}</li>
</ul>

<p><strong>Pesan:</strong></p>
<p>{{ $contactMessage->message }}</p>

<p>Kelola di panel admin untuk menindaklanjuti.</p>
