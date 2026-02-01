<?php

namespace App\Livewire\Traps;

use App\Services\TrapService;
use Livewire\Component;

class FakeSalaryLeakButton extends Component
{
    public $trapConfig;
    public $buttonLabel;
    public $buttonIcon;

    public function mount($trapConfigId = null, $label = 'تسريب الرواتب', $icon = '💰')
    {
        $this->trapConfig = $trapConfigId;
        $this->buttonLabel = $label;
        $this->buttonIcon = $icon;
    }

    public function triggerTrap()
    {
        // تسجيل الفخ
        $trapService = app(TrapService::class);
        
        try {
            $trapService->logTrapTrigger(
                trapConfigId: $this->trapConfig ?? 1, // Default trap ID
                userId: auth()->id(),
                additionalData: [
                    'button_label' => $this->buttonLabel,
                    'component' => 'FakeSalaryLeakButton',
                ]
            );

            // لا نعرض أي رسالة خطأ - نتظاهر أن كل شيء طبيعي
            // يمكن عرض loader وهمي
            $this->dispatch('show-fake-loader');
            
        } catch (\Exception $e) {
            \Log::error('Trap trigger failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.traps.fake-salary-leak-button');
    }
}
