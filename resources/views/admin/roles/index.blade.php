@extends('components.layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto pb-12">
    <div class="mb-8">
        <h1 class="font-serif text-3xl md:text-4xl text-sage-900 dark:text-cream-100">Gestión de acciones por rol</h1>
        <p class="text-ink-500 dark:text-sage-300 mt-1">Solo el administrador puede asignar qué acciones pueden ejecutar los roles del sistema.</p>
    </div>

    @if(session('success'))
        <div class="alert-auto-dismiss mb-6 flex items-center gap-3 rounded-2xl border border-sage-200 bg-sage-50 px-5 py-4 text-sage-800">
            <svg class="w-5 h-5 text-sage-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.roles.update', $selectedRoleModel) }}" method="POST" class="surface p-6 mb-6">
        @csrf
        @method('PUT')

        <div class="flex flex-col gap-6 mb-6 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="font-serif text-2xl text-sage-900 dark:text-cream-100">Gestión de roles</h2>
                <p class="text-ink-500 dark:text-sage-300 text-sm mt-1">Selecciona un rol y ajusta sus permisos.</p>
            </div>
            <div class="w-full md:w-80">
                <label class="label mb-2">Rol</label>
                <select id="roleSelector" name="role" class="field w-full">
                    @foreach($roleOptions as $key => $label)
                        <option value="{{ $key }}" {{ $selectedRole === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php
            $previewNavItems = [
                ['actions' => [], 'label' => 'Resumen diario', 'key' => 'dashboard'],
                ['actions' => ['book_appointments'], 'label' => 'Agendar cita', 'key' => 'booking'],
                ['actions' => ['view_reports'], 'label' => 'Reportes', 'key' => 'reportes'],
                ['actions' => ['citas_index'], 'label' => 'Citas', 'key' => 'citas'],
                ['actions' => ['pacientes_index'], 'label' => 'Pacientes', 'key' => 'pacientes'],
                ['actions' => ['manage_services'], 'label' => 'Servicios', 'key' => 'servicios'],
                ['actions' => ['manage_specialists'], 'label' => 'Especialistas', 'key' => 'especialistas'],
                ['actions' => ['manage_users'], 'label' => 'Usuarios', 'key' => 'usuarios'],
                ['actions' => ['roles_index'], 'label' => 'Roles', 'key' => 'roles'],
                ['actions' => ['categorias_index'], 'label' => 'Categorías', 'key' => 'categorias'],
                ['actions' => ['lista_espera_index'], 'label' => 'Lista de espera', 'key' => 'lista-espera'],
                ['actions' => ['soporte_index'], 'label' => 'Soporte', 'key' => 'soporte'],
            ];
            $selectedActions = $selectedRoleModel->acciones ?? [];
        @endphp

        <div class="surface mb-6 p-5 rounded-3xl border border-cream-200 bg-cream-50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-ink-900 dark:text-cream-100">Preview de topbar</h3>
                    <p class="text-sm text-ink-500 dark:text-sage-300">Esta vista muestra los elementos que aparecerían en la barra superior según las acciones marcadas para el rol.</p>
                </div>
            </div>
            <div id="roleTopbarPreview" class="flex flex-wrap gap-3">
                @foreach($previewNavItems as $item)
                    @php
                        $isVisible = collect($item['actions'])->every(fn($action) => in_array($action, $selectedActions));
                    @endphp
                    <span class="topbar-preview-item rounded-full border border-cream-300 bg-white px-4 py-2 text-sm font-semibold text-ink-700 shadow-sm {{ $isVisible ? '' : 'hidden' }}" data-actions="{{ implode(',', $item['actions']) }}">{{ $item['label'] }}</span>
                @endforeach
                <span id="roleTopbarPreviewEmpty" class="text-sm text-ink-500 {{ collect($previewNavItems)->contains(fn($item) => collect($item['actions'])->every(fn($action) => in_array($action, $selectedActions))) ? 'hidden' : '' }}">No hay elementos de topbar activos para este rol.</span>
            </div>
        </div>

        @php
            $primary = ['pacientes', 'citas'];
            $other = collect($groups)->keys()->diff($primary)->values()->all();
            $tabs = array_merge(array_filter($primary, fn($k) => isset($groups[$k])), $other);
        @endphp

        <div class="mb-6 rounded-3xl border border-cream-200 bg-cream-50 p-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @foreach($tabs as $groupKey)
                    <button type="button" class="tab-btn inline-flex items-center justify-center rounded-full border border-cream-300 bg-white px-4 py-2 text-sm font-semibold text-ink-700 transition hover:bg-sage-100" data-tab="{{ $groupKey }}">{{ $groups[$groupKey]['label'] }}</button>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            @php
                $grouped = collect($groups)->flatMap(fn($g) => $g['actions'])->unique()->values()->all();
                $ungrouped = collect($availableActions)->keys()->diff($grouped)->all();
            @endphp

            @foreach($tabs as $t)
                <div class="tab-panel" data-tab-panel="{{ $t }}" style="display: none;">
                    <div class="rounded-2xl border border-cream-200 p-4 mb-6">
                        <div class="flex items-center justify-between mb-3 gap-4 flex-wrap">
                            <div>
                                <div class="font-semibold text-ink-900 dark:text-cream-100">{{ $groups[$t]['label'] ?? ucfirst($t) }}</div>
                                <p class="text-sm text-ink-500 dark:text-sage-300 mt-1">Acciones relacionadas con {{ strtolower($groups[$t]['label'] ?? $t) }}.</p>
                            </div>
                            <label class="inline-flex items-center gap-2 cursor-pointer rounded-full bg-cream-100 px-4 py-2 text-sm text-ink-700">
                                <input type="checkbox" class="group-toggle h-5 w-5" data-group="{{ $t }}">
                                <span>Seleccionar todo</span>
                            </label>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach($groups[$t]['actions'] as $actionKey)
                                @php $actionMeta = $availableActions[$actionKey] ?? null; @endphp
                                @if($actionMeta)
                                    <label class="surface surface-hover rounded-3xl p-4 flex items-center gap-4 cursor-pointer">
                                        <input type="checkbox" name="acciones[]" value="{{ $actionKey }}" class="h-5 w-5 accent-sage-600" {{ in_array($actionKey, $selectedRoleModel->acciones ?? []) ? 'checked' : '' }}>
                                        <span class="font-semibold text-ink-900 dark:text-cream-100">{{ $actionMeta['label'] }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" name="scope" value="{{ $t }}" class="btn-primary px-5 py-2 text-sm uppercase tracking-[0.12em]">Actualizar</button>
                    </div>
                </div>
            @endforeach

            @if(!empty($ungrouped))
                <div class="rounded-2xl border border-cream-200 p-4">
                    <div class="font-semibold text-ink-900 dark:text-cream-100 mb-3">Otros permisos</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($ungrouped as $actionKey)
                            @php $actionMeta = $availableActions[$actionKey] ?? null; @endphp
                            @if($actionMeta)
                                <label class="surface surface-hover rounded-3xl p-4 flex items-center gap-4 cursor-pointer">
                                    <input type="checkbox" name="acciones[]" value="{{ $actionKey }}" class="h-5 w-5 accent-sage-600" {{ in_array($actionKey, $selectedRoleModel->acciones ?? []) ? 'checked' : '' }}>
                                    <span class="font-semibold text-ink-900 dark:text-cream-100">{{ $actionMeta['label'] }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end mt-3">
                    <button type="submit" name="scope" value="otros" class="btn-primary px-5 py-2 text-sm uppercase tracking-[0.12em]">Actualizar</button>
                </div>
            @endif
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelector = document.getElementById('roleSelector');
        if (roleSelector) {
            roleSelector.addEventListener('change', function() {
                const role = this.value;
                const url = new URL(window.location.href);
                url.searchParams.set('role', role);
                window.location.href = url.toString();
            });
        }

        const tabBtns = document.querySelectorAll('.tab-btn');
        const panels = document.querySelectorAll('.tab-panel');
        function showTab(key) {
            panels.forEach(p => p.style.display = p.dataset.tabPanel === key ? 'block' : 'none');
            tabBtns.forEach(b => {
                const active = b.dataset.tab === key;
                b.classList.toggle('bg-sage-600', active);
                b.classList.toggle('text-cream-100', active);
                b.classList.toggle('bg-white', !active);
                b.classList.toggle('text-ink-700', !active);
            });
        }
        if (tabBtns.length) {
            showTab(tabBtns[0].dataset.tab);
            tabBtns.forEach(b => b.addEventListener('click', () => showTab(b.dataset.tab)));
        }

        window.roleGroups = @json(collect($groups)->mapWithKeys(fn($group, $key) => [$key => $group['actions']])->toArray());

        const updateTopbarPreview = () => {
            const previewItems = document.querySelectorAll('#roleTopbarPreview .topbar-preview-item');
            const selectedValues = Array.from(document.querySelectorAll('input[name="acciones[]"]:checked')).map(i => i.value);
            let anyVisible = false;

            previewItems.forEach(item => {
                const required = item.dataset.actions.split(',');
                const visible = required.every(action => selectedValues.includes(action));
                item.style.display = visible ? 'inline-flex' : 'none';
                if (visible) anyVisible = true;
            });

            document.getElementById('roleTopbarPreviewEmpty').classList.toggle('hidden', anyVisible);
        };

        document.querySelectorAll('.group-toggle').forEach(function(groupChk) {
            const group = groupChk.dataset.group;
            const members = document.querySelectorAll('input[name="acciones[]"][value]');
            const groupMemberInputs = Array.from(members).filter(function(m) {
                const action = m.value;
                return (window.roleGroups[group] || []).indexOf(action) !== -1;
            });
            const allChecked = groupMemberInputs.length > 0 && groupMemberInputs.every(i => i.checked);
            groupChk.checked = allChecked;
            groupChk.addEventListener('change', function() {
                groupMemberInputs.forEach(i => i.checked = groupChk.checked);
                updateTopbarPreview();
            });
        });

        document.querySelectorAll('input[name="acciones[]"]').forEach(function(chk) {
            chk.addEventListener('change', updateTopbarPreview);
        });

        updateTopbarPreview();
    });
</script>
@endsection
