<?php

namespace App\Livewire\Admin;

use App\Models\Marketing\CampaignRule;
use App\Models\Marketing\MarketingCampaign;
use App\Services\CampaignService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.main')]
#[Title('Kelola Kampanye - AbodaFarm')]
class CampaignManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $searchQuery = '';
    public string $statusFilter = 'all';
    public string $typeFilter = 'all';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDetailModal = false;
    public ?MarketingCampaign $selectedCampaign = null;

    // Form fields
    public string $name = '';
    public string $campaign_type = 'manual';
    public string $description = '';
    public string $status = 'draft';
    public string $start_date = '';
    public string $end_date = '';
    public int $priority = 0;
    public bool $auto_generate_voucher = false;
    public ?int $voucher_quantity = null;
    public ?int $max_claim = 1;
    public ?float $budget_limit = null;

    // Rules
    public array $rules = [];

    protected $campaignService;

    public function mount(CampaignService $campaignService): void
    {
        $this->campaignService = $campaignService;
        $this->start_date = now()->format('Y-m-d\TH:i');
        $this->end_date = now()->addMonth()->format('Y-m-d\TH:i');
    }

    public function getCampaignsProperty()
    {
        return MarketingCampaign::query()
            ->with('creator')
            ->when($this->searchQuery, fn($q) => $q->where('name', 'like', "%{$this->searchQuery}%"))
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter !== 'all', fn($q) => $q->where('campaign_type', $this->typeFilter))
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function getStatusesProperty(): array
    {
        return MarketingCampaign::getStatuses();
    }

    public function getCampaignTypesProperty(): array
    {
        return MarketingCampaign::getCampaignTypes();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(MarketingCampaign $campaign): void
    {
        $this->selectedCampaign = $campaign;
        $this->name = $campaign->name;
        $this->campaign_type = $campaign->campaign_type;
        $this->description = $campaign->description ?? '';
        $this->status = $campaign->status;
        $this->start_date = $campaign->start_date->format('Y-m-d\TH:i');
        $this->end_date = $campaign->end_date->format('Y-m-d\TH:i');
        $this->priority = $campaign->priority;
        $this->auto_generate_voucher = $campaign->auto_generate_voucher;
        $this->voucher_quantity = $campaign->voucher_quantity;
        $this->max_claim = $campaign->max_claim ?? 1;
        $this->budget_limit = $campaign->budget_limit;
        $this->rules = $campaign->rules->map(fn($r) => [
            'rule_type' => $r->rule_type,
            'operator' => $r->operator,
            'value' => is_array($r->value) ? implode(',', $r->value) : $r->value,
        ])->toArray();

        $this->showEditModal = true;
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDetailModal = false;
        $this->selectedCampaign = null;
        $this->resetForm();
    }

    public function viewDetail(MarketingCampaign $campaign): void
    {
        $this->selectedCampaign = $campaign->load(['creator', 'rules']);
        $this->showDetailModal = true;
    }

    protected function resetForm(): void
    {
        $this->name = '';
        $this->campaign_type = 'manual';
        $this->description = '';
        $this->status = 'draft';
        $this->start_date = now()->format('Y-m-d\TH:i');
        $this->end_date = now()->addMonth()->format('Y-m-d\TH:i');
        $this->priority = 0;
        $this->auto_generate_voucher = false;
        $this->voucher_quantity = null;
        $this->max_claim = 1;
        $this->budget_limit = null;
        $this->rules = [];
    }

    public function saveCampaign(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'campaign_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $data = [
            'name' => $this->name,
            'campaign_type' => $this->campaign_type,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'priority' => $this->priority,
            'auto_generate_voucher' => $this->auto_generate_voucher,
            'voucher_quantity' => $this->voucher_quantity,
            'max_claim' => $this->max_claim,
            'budget_limit' => $this->budget_limit,
        ];

        $campaign = $this->campaignService->createCampaign($data);

        if (!empty($this->rules)) {
            $this->saveRules($campaign);
        }

        if ($campaign->auto_generate_voucher && $campaign->status === 'active') {
            $this->campaignService->generateVouchers($campaign);
        }

        $this->closeModal();
        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Kampanye berhasil dibuat!',
        ]);
    }

    public function updateCampaign(): void
    {
        if (!$this->selectedCampaign) {
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'campaign_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $data = [
            'name' => $this->name,
            'campaign_type' => $this->campaign_type,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'priority' => $this->priority,
            'auto_generate_voucher' => $this->auto_generate_voucher,
            'voucher_quantity' => $this->voucher_quantity,
            'max_claim' => $this->max_claim,
            'budget_limit' => $this->budget_limit,
        ];

        $campaign = $this->campaignService->updateCampaign($this->selectedCampaign, $data);

        $this->selectedCampaign->rules()->delete();
        if (!empty($this->rules)) {
            $this->saveRules($campaign);
        }

        $this->closeModal();
        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Kampanye berhasil diupdate!',
        ]);
    }

    protected function saveRules(MarketingCampaign $campaign): void
    {
        foreach ($this->rules as $index => $rule) {
            $values = is_string($rule['value'])
                ? array_map('trim', explode(',', $rule['value']))
                : $rule['value'];

            $campaign->rules()->create([
                'rule_type' => $rule['rule_type'],
                'operator' => $rule['operator'],
                'value' => $values,
                'priority' => $index,
            ]);
        }
    }

    public function addRule(): void
    {
        $this->rules[] = [
            'rule_type' => 'minimum_order',
            'operator' => 'greater_or_equal',
            'value' => '100000',
        ];
    }

    public function removeRule(int $index): void
    {
        unset($this->rules[$index]);
        $this->rules = array_values($this->rules);
    }

    public function generateVouchers(MarketingCampaign $campaign): void
    {
        $result = $this->campaignService->generateVouchers($campaign);

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => "{$result['generated']} voucher berhasil dibuat!",
        ]);
    }

    public function toggleStatus(MarketingCampaign $campaign): void
    {
        $newStatus = $campaign->status === 'active' ? 'paused' : 'active';
        $campaign->update(['status' => $newStatus]);

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Status kampanye berhasil diupdate!',
        ]);
    }

    public function getRuleTypesProperty(): array
    {
        return CampaignRule::getRuleTypes();
    }

    public function getOperatorsProperty(): array
    {
        return CampaignRule::getOperators();
    }

    public function render()
    {
        return view('livewire.admin.campaign-management', [
            'campaigns' => $this->campaigns,
        ]);
    }
}
