<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Edit Pengumuman') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('pengumuman.update', $pengumuman) }}" method="POST" id="pengumumanForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="judul" value="Judul Pengumuman" />
                            <x-text-input id="judul" name="judul" type="text" class="mt-1 block w-full"
                                :value="old('judul', $pengumuman->judul)" required autofocus />
                            <x-input-error :messages="$errors->get('judul')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label value="Konten" />
                            <div id="editorjs" class="mt-1 block w-full rounded-md border border-gray-300 bg-white">
                            </div>
                            <textarea name="konten" id="konten" class="hidden">{{ old('konten', $pengumuman->konten) }}</textarea>
                            <textarea name="konten_json" id="konten_json" class="hidden">{{ old('konten_json', $pengumuman->konten_json) }}</textarea>
                            <x-input-error :messages="$errors->get('konten')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    {{ old('is_active', $pengumuman->is_active) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-600">Aktif</span>
                            </label>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('pengumuman.index') }}"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Batal</a>
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.30.7"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@2.8.7"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@2.0.9"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/paragraph@2.11.5"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@2.10.3/dist/image.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@2.5.3"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@1.4.1"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@2.6.0"></script>
<style>
    .codex-editor h2.ce-header {
        font-size: 1.5em;
        font-weight: 700;
        line-height: 1.3;
        margin: 0.5em 0;
    }

    .codex-editor h3.ce-header {
        font-size: 1.25em;
        font-weight: 700;
        line-height: 1.3;
        margin: 0.5em 0;
    }

    .codex-editor h4.ce-header {
        font-size: 1.1em;
        font-weight: 700;
        line-height: 1.3;
        margin: 0.5em 0;
    }

    .codex-editor .ce-paragraph {
        line-height: 1.6;
        margin: 0.3em 0;
    }

    .codex-editor .cdx-list {
        padding-left: 1.5em;
        margin: 0.3em 0;
    }

    .codex-editor .cdx-list--ordered {
        list-style: decimal;
    }

    .codex-editor .cdx-list--unordered {
        list-style: disc;
    }

    .codex-editor .cdx-list__item {
        padding: 0.1em 0;
    }

    .codex-editor .cdx-block {
        padding: 0.4em 0;
    }

    .codex-editor .cdx-quote {
        border-left: 4px solid #d1d5db;
        padding-left: 1em;
        font-style: italic;
        color: #4b5563;
        margin: 0.5em 0;
    }

    .codex-editor .cdx-quote__text {
        min-height: 0;
    }

    .codex-editor .cdx-quote__caption {
        margin-top: 0.3em;
        font-size: 0.9em;
    }

    .codex-editor .cdx-delimiter {
        line-height: 1.6em;
        width: 100%;
        text-align: center;
        margin: 0.5em 0;
    }

    .codex-editor .cdx-delimiter:before {
        display: inline-block;
        content: "***";
        font-size: 1.5em;
        letter-spacing: 0.2em;
        color: #9ca3af;
    }

    .codex-editor .image-tool__image {
        max-width: 100%;
        border-radius: 4px;
    }

    .codex-editor .image-tool__caption {
        text-align: center;
        font-size: 0.85em;
        color: #6b7280;
        margin-top: 0.3em;
    }

    .ce-toolbar__plus,
    .ce-toolbar__settings-btn {
        color: #4f46e5;
    }

    .ce-toolbar__plus:hover,
    .ce-toolbar__settings-btn:hover {
        background: #e0e7ff;
    }

    .ce-popover {
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .ce-popover__item:hover {
        background: #eef2ff;
    }

    .ce-inline-toolbar {
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }

    .ce-inline-tool:hover {
        background: #eef2ff;
    }

    .ce-conversion-tool:hover {
        background: #eef2ff;
    }

    .ce-conversion-tool--focused {
        background: #e0e7ff !important;
    }
</style>
<script>
    (function() {
        function init() {
            if (typeof EditorJS === 'undefined') {
                setTimeout(init, 200);
                return;
            }
            const existingData = document.getElementById('konten_json').value;
            const data = existingData ? JSON.parse(existingData) : null;
            const ListTool = window.EditorjsList || window.List;
            const editor = new EditorJS({
                holder: 'editorjs',
                tools: {
                    header: {
                        class: Header,
                        config: {
                            levels: [2, 3, 4],
                            defaultLevel: 2
                        }
                    },
                    paragraph: {
                        class: Paragraph,
                        inlineToolbar: true
                    },
                    list: {
                        class: ListTool,
                        inlineToolbar: true
                    },
                    image: {
                        class: ImageTool,
                        config: {
                            endpoints: {
                                byFile: '{{ route('pengumuman.upload-image') }}',
                                byUrl: '{{ route('pengumuman.upload-image-url') }}',
                            },
                            additionalRequestHeaders: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }
                    },
                    embed: {
                        class: Embed,
                        inlineToolbar: true
                    },
                    delimiter: Delimiter,
                    quote: {
                        class: Quote,
                        inlineToolbar: true
                    },
                },
                placeholder: 'Tulis pengumuman di sini...',
                data: data,
                onReady: function() {
                    const btn = document.querySelector('#pengumumanForm button[type="submit"]');
                    if (btn) {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            editor.save().then(out => {
                                document.getElementById('konten_json').value = JSON
                                    .stringify(out);
                                document.getElementById('konten').value = (function(d) {
                                    if (!d || !d.blocks) return '';
                                    let h = '';
                                    d.blocks.forEach(b => {
                                        switch (b.type) {
                                            case 'paragraph':
                                                h +=
                                                    `<p>${b.data.text}</p>`;
                                                break;
                                            case 'header':
                                                h +=
                                                    `<h${b.data.level}>${b.data.text}</h${b.data.level}>`;
                                                break;
                                            case 'list':
                                                h +=
                                                    `<${b.data.style === 'ordered' ? 'ol' : 'ul'}>${b.data.items.map(i => `<li>${i}</li>`).join('')}</${b.data.style === 'ordered' ? 'ol' : 'ul'}>`;
                                                break;
                                            case 'image':
                                                h +=
                                                    `<figure><img src="${b.data.file?.url || b.data.url || ''}" style="max-width:100%;height:auto" />${b.data.caption ? '<figcaption>'+b.data.caption+'</figcaption>' : ''}</figure>`;
                                                break;
                                            case 'embed':
                                                h +=
                                                    `<div class="embed-wrapper"><iframe src="${b.data.embed}" width="${b.data.width || 600}" height="${b.data.height || 400}" frameborder="0"></iframe></div>`;
                                                break;
                                            case 'quote':
                                                h +=
                                                    `<blockquote class="border-l-4 border-gray-300 pl-4 italic">${b.data.text}${b.data.caption ? '<br><cite>— '+b.data.caption+'</cite>' : ''}</blockquote>`;
                                                break;
                                            case 'delimiter':
                                                h +=
                                                    `<hr class="my-4">`;
                                                break;
                                        }
                                    });
                                    return h;
                                })(out);
                                document.getElementById('pengumumanForm').submit();
                            }).catch(() => document.getElementById('pengumumanForm')
                                .submit());
                        });
                    }
                }
            });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
        else init();
    })();
</script>
