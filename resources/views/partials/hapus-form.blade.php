<form method="POST" action="{{ $action }}">
    @csrf
    @method('DELETE')
    <button type="submit">Hapus</button>
</form>
