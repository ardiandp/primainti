Ada pesan kontak baru dari website:

Nama: {{ $contactMessage->name }}
Email: {{ $contactMessage->email }}
@if ($contactMessage->company)
Perusahaan: {{ $contactMessage->company }}
@endif
@if ($contactMessage->phone)
Telepon: {{ $contactMessage->phone }}
@endif
Subjek: {{ $contactMessage->subject }}

Pesan:
{{ $contactMessage->message }}

Kelola di panel admin untuk menindaklanjuti.
