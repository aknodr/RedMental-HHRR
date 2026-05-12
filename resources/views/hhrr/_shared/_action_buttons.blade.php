{{--
  Reusable row action buttons for HHRR list pages.
  Required vars:
    $showRoute    (string|null) — route URL for show; omit if not applicable
    $editRoute    (string|null) — route URL for edit; omit if no edit perm
    $deleteRoute  (string|null) — route URL for destroy; omit if no delete perm
    $deleteLabel  (string)      — label shown in the SweetAlert confirm
--}}
<div class="flex items-center justify-end gap-1.5">
    @if(!empty($showRoute))
        <a href="{{ $showRoute }}"
           class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition">
            <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
        </a>
    @endif
    @if(!empty($editRoute))
        <a href="{{ $editRoute }}"
           class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold rounded-lg transition">
            <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
        </a>
    @endif
    @if(!empty($deleteRoute))
        <form method="POST" action="{{ $deleteRoute }}" data-confirm-delete="{{ $deleteLabel ?? 'this record' }}">
            @csrf @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-lg transition">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
            </button>
        </form>
    @endif
</div>
