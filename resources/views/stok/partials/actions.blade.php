<td class="text-center">
    <button class="aksi-edit"><a href="{{ route('stok.edit', $stok->id) }}"><i class='bx bx-edit-alt'></i></a></button>

    <form action="{{ route('stok.destroy', $stok->id) }}"
        method="POST"
        onsubmit="return confirm('Are you sure?')"
        style="display: inline;">
            @csrf 
            @method('DELETE')
                               
    <button type="submit" class="aksi-delete"><i class='bx bx-trash'></i></button>
    </form>
</td>          