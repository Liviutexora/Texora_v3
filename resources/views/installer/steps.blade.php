<div class="flex items-center justify-center space-x-2">
    @php
        $steps = [
            'welcome' => __('installer.Welcome'),
            'requirements' => __('installer.Requirements'),
            'permissions' => __('installer.Permissions'),
            'database' => __('installer.Database'),
            'admin' => __('installer.Admin'),
            'finish' => __('installer.Finish')
        ];
        $currentStep = request()->route()->getName();
        $currentStepIndex = array_search(str_replace('installer.', '', $currentStep), array_keys($steps));
    @endphp

    @foreach($steps as $key => $label)
        @php
            $stepIndex = array_search($key, array_keys($steps));
            $isCompleted = $stepIndex < $currentStepIndex;
            $isCurrent = 'installer.' . $key === $currentStep;
        @endphp
        
        <div class="flex items-center">
            <div class="relative flex items-center justify-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm
                    {{ $isCompleted ? 'bg-green-500 text-white' : ($isCurrent ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-600') }}">
                    {{ $stepIndex + 1 }}
                </div>
            </div>
            @if(!$loop->last)
                <div class="w-12 h-0.5 {{ $isCompleted ? 'bg-green-500' : 'bg-gray-300' }}"></div>
            @endif
        </div>
    @endforeach
</div>