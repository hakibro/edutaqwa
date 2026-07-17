@forelse ($pengumumans as $p)
    <tr>
        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $p->judul }}</td>
        <td class="whitespace-nowrap px-6 py-4 text-sm">
            @if ($p->is_active)
                <span
                    class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">Aktif</span>
            @else
                <span
                    class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-600">Nonaktif</span>
            @endif
        </td>
        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $p->creator?->name ?? '-' }}</td>
        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $p->created_at->format('d M Y H:i') }}</td>
        <td class="whitespace-nowrap px-6 py-4 text-sm text-right">
            <a href="{{ route('pengumuman.edit', $p) }}"
                class="font-medium text-indigo-600 hover:text-indigo-900">Edit</a>
            <form action="{{ route('pengumuman.destroy', $p) }}" method="POST" class="inline-block ml-2"
                onsubmit="return confirm('Hapus pengumuman ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="font-medium text-red-600 hover:text-red-900">Hapus</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada pengumuman.</td>
    </tr>
@endforelse
