    <button class="aksi-show">
        <a href="{{ route('departemen.show', $departemen->id) }}" class="btn btn-sm btn-info aksi-show">
            <i class="bx bx-show"></i>
        </a>
    </button>

    <button class="aksi-edit">
        <a href="{{ route('departemen.edit', $departemen->id) }}" class="btn btn-sm btn-warning aksi-edit">
            <i class="bx bx-edit-alt"></i>
        </a>
    </button>

        @if ($departemen->inventori()->exists())
            <button type="button" class="aksi-delete"
                    onclick="alert('Departemen tidak bisa dihapus karena masih memiliki data inventori')">
                <i class='bx bx-trash'></i>
            </button>
        @else
            <form action="{{ route('departemen.destroy', $departemen->id) }}"
                method="POST"
                onsubmit="return confirm('Are you sure?')"
                style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="aksi-delete">
                    <i class='bx bx-trash'></i>
                </button>
            </form>
        @endif
