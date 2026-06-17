<x-ess-layout
    :title="collect($tabs)->firstWhere('id', $activeTab)['label'] ?? __('Overview')"
    :active-tab="$activeTab"
    :tabs="$tabs"
>
    @includeWhen($activeTab === 'overview', 'ess.tabs.overview')
    @includeWhen($activeTab === 'profile', 'ess.tabs.profile')
    @includeWhen($activeTab === 'payslips', 'ess.tabs.payslips')
    @includeWhen($activeTab === 'payroll-history', 'ess.tabs.payroll-history')
    @includeWhen($activeTab === 'documents', 'ess.tabs.documents')
    @includeWhen($activeTab === 'security', 'ess.tabs.security')
    @includeWhen($activeTab === 'communications', 'ess.tabs.communications')
    @includeWhen($activeTab === 'onboarding', 'ess.tabs.onboarding')
</x-ess-layout>
