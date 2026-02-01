<?php

namespace App\Livewire\Traps;

use App\Services\TrapService;
use Livewire\Component;

class ProhibitedSectionTrap extends Component
{
    public $trapConfig;
    public $sectionTitle;
    public $sectionIcon;
    public $description;

    public function mount($trapConfigId = null, $title = 'بيانات سرية', $icon = '🔒', $description = 'قسم مخصص للإدارة العليا فقط')
    {
        $this->trapConfig = $trapConfigId;
        $this->sectionTitle = $title;
        $this->sectionIcon = $icon;
        $this->description = $description;
    }

    public function attemptAccess()
    {
        // تسجيل محاولة الوصول
        $trapService = app(TrapService::class);
        
        try {
            $trapService->logTrapTrigger(
                trapConfigId: $this->trapConfig ?? 2,
                userId: auth()->id(),
                additionalData: [
                    'section_title' => $this->sectionTitle,
                    'component' => 'ProhibitedSectionTrap',
                    'action' => 'attempted_access',
                ]
            );

            // عرض رسالة وهمية "ليس لديك صلاحية"
            $this->dispatch('show-fake-permission-error');
            
        } catch (\Exception $e) {
            \Log::error('Trap trigger failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.traps.prohibited-section-trap');
    }
}
