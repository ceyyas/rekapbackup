<button class="aksi-show">
    <a href="{{ route('komputer.show', $row->id) }}">
        <i class='bx bx-show'></i>
    </a>
</button>

<button class="aksi-edit">
    <a href="{{ route('komputer.edit', $row->id) }}">
        <i class='bx bx-edit-alt'></i>
    </a>
</button>

<form action="{{ route('komputer.destroy', $row->id) }}"
      method="POST"
      onsubmit="return confirm('Are you sure?')"
      style="display: inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="aksi-delete">
        <i class='bx bx-trash'></i>
    </button>
</form>
