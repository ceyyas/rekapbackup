@foreach ($query as $index => $inventori)
<tr>
    <td>{{ $index + 1 }}</td>
    <td>{{ $inventori->perusahaan->nama_perusahaan }}</td>
    <td>{{ $inventori->departemen->nama_departemen }}</td>
    <td>{{ $inventori->hostname }}</td>
    <td>{{ $inventori->username }}</td>
    <td>{{ $inventori->email }}</td>
    <td>{{ $inventori->kategori }}</td>
    <td>
        <span class="status 
            @if($inventori->status === 'active') status-active 
            @elseif($inventori->status === 'inactive') status-inactive
            @endif">
            {{ ucfirst($inventori->status) }}
        </span>
    </td>
    <td class="text-center">
        <button class="aksi-show"><a href="{{ route('komputer.show', $inventori->id) }}"><i class='bx bx-show'></i></a></button>
        <button class="aksi-edit"><a href="{{ route('komputer.edit', $inventori->id) }}"><i class='bx bx-edit-alt'></i></a></button>
        <form action="{{ route('komputer.destroy', $inventori->id) }}"
            method="POST"
            onsubmit="return confirm('Are you sure?')"
            style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="aksi-delete"><i class='bx bx-trash'></i></button>
        </form>
    </td>
</tr>
@endforeach
