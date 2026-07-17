@forelse ($kelas as $k)
    <tr>
        <td class="px-6 py-4 text-sm text-gray-900">{{ $k->nama }}</td>
        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
            {{ $k->tingkat }}</td>
        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
            {{ $k->jurusan?->nama ?? '-' }}</td>
        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
            {{ $k->lembaga->nama }}</td>
        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
            {{ $k->siswa_count }}</td>
        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
            <a href="{{ route('kelas.edit', $k) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
            <form action="{{ route('kelas.destroy', $k) }}" method="POST" class="inline"
                onsubmit="return confirm('Hapus kelas ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="ml-2 text-red-600 hover:text-red-900">Hapus</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada
            data kelas.</td>
    </tr>
@endforelse
