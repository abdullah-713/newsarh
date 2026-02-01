<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Profile Information --}}
        <x-filament::section>
            <x-slot name="heading">
                المعلومات الشخصية
            </x-slot>

            <form wire:submit="updateProfile">
                {{ $this->form }}

                <div class="mt-6">
                    <x-filament::button type="submit">
                        حفظ التغييرات
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Password Change --}}
        <x-filament::section>
            <x-slot name="heading">
                تغيير كلمة المرور
            </x-slot>

            <form wire:submit="updatePassword">
                <div class="space-y-4">
                    <div>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="password"
                                wire:model="passwordData.current_password"
                                placeholder="كلمة المرور الحالية"
                            />
                        </x-filament::input.wrapper>
                        @error('passwordData.current_password')
                            <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="password"
                                wire:model="passwordData.password"
                                placeholder="كلمة المرور الجديدة"
                            />
                        </x-filament::input.wrapper>
                        @error('passwordData.password')
                            <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="password"
                                wire:model="passwordData.password_confirmation"
                                placeholder="تأكيد كلمة المرور الجديدة"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <x-filament::button type="submit" color="warning">
                        تحديث كلمة المرور
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Account Info (Read-only) --}}
        <x-filament::section>
            <x-slot name="heading">
                معلومات الحساب
            </x-slot>

            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">رقم الموظف</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ auth()->user()->employee_id ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">الفرع</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ auth()->user()->branch?->name ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">القسم</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ auth()->user()->department?->name ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">المسمى الوظيفي</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ auth()->user()->jobTitle?->title ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">تاريخ التوظيف</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ auth()->user()->hire_date?->format('Y-m-d') ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">نوع العقد</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        @if(auth()->user()->contract_type === 'full_time')
                            دوام كامل
                        @elseif(auth()->user()->contract_type === 'part_time')
                            دوام جزئي
                        @else
                            -
                        @endif
                    </dd>
                </div>
            </dl>
        </x-filament::section>
    </div>
</x-filament-panels::page>
