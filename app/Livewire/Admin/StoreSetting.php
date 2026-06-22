<?php

namespace App\Livewire\Admin;

use App\Models\DeliveryArea;
use App\Models\Faq;
use App\Models\FarmStatistic;
use App\Models\HeroSlide;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.main')]
#[Title('Pengaturan Toko')]
class StoreSetting extends Component
{
    use WithFileUploads;

    // Tab
    public string $activeTab = 'testimonials';

    // Delete Confirmation
    public ?int $pendingDeleteId = null;
    public string $pendingDeleteType = '';

    // ==================== TESTIMONIALS ====================
    public ?int $editingTestimonialId = null;
    public string $testimonialName = '';
    public string $testimonialRole = '';
    public string $testimonialContent = '';
    public int $testimonialRating = 5;
    public $testimonialImage = null;
    public ?string $testimonialImagePath = null;
    public bool $testimonialIsActive = true;
    public int $testimonialSortOrder = 0;
    public bool $showTestimonialModal = false;

    // ==================== FAQs ====================
    public ?int $editingFaqId = null;
    public string $faqQuestion = '';
    public string $faqAnswer = '';
    public string $faqCategory = 'product';
    public bool $faqIsActive = true;
    public int $faqSortOrder = 0;
    public bool $showFaqModal = false;

    // ==================== HERO SLIDES ====================
    public ?int $editingHeroSlideId = null;
    public string $heroSlideTitle = '';
    public string $heroSlideSubtitle = '';
    public $heroSlideImage = null;
    public ?string $heroSlideImagePath = null;
    public string $heroSlideButtonText = '';
    public string $heroSlideButtonUrl = '';
    public string $heroSlideButtonTarget = '_self';
    public int $heroSlideSortOrder = 0;
    public bool $heroSlideIsActive = true;
    public bool $showHeroSlideModal = false;

    // ==================== STATISTICS ====================
    public ?int $editingStatisticId = null;
    public string $statisticKey = '';
    public string $statisticValue = '';
    public string $statisticLabel = '';
    public string $statisticIcon = '';
    public int $statisticSortOrder = 0;
    public bool $showStatisticModal = false;

    // ==================== DELIVERY AREAS ====================
    public ?int $editingDeliveryAreaId = null;
    public string $deliveryAreaRegion = '';
    public string $deliveryAreaCity = '';
    public string $deliveryAreaProvince = 'Sulawesi Utara';
    public string $deliveryAreaFee = '0';
    public int $deliveryAreaEstimatedDays = 1;
    public bool $deliveryAreaIsActive = true;
    public int $deliveryAreaSortOrder = 0;
    public bool $showDeliveryAreaModal = false;

    // ==================== TABS ====================
    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    // ==================== TESTIMONIAL CRUD ====================
    public function openTestimonialModal(?int $id = null)
    {
        if ($id) {
            $t = Testimonial::findOrFail($id);
            $this->editingTestimonialId = $t->id;
            $this->testimonialName = $t->name;
            $this->testimonialRole = $t->role;
            $this->testimonialContent = $t->content;
            $this->testimonialRating = $t->rating;
            $this->testimonialImagePath = $t->image;
            $this->testimonialIsActive = $t->is_active;
            $this->testimonialSortOrder = $t->sort_order;
        } else {
            $this->resetTestimonialForm();
        }
        $this->showTestimonialModal = true;
    }

    public function closeTestimonialModal()
    {
        $this->showTestimonialModal = false;
        $this->resetTestimonialForm();
    }

    public function saveTestimonial()
    {
        $this->validate([
            'testimonialName' => 'required|string|max:255',
            'testimonialRole' => 'required|string|max:255',
            'testimonialContent' => 'required|string',
            'testimonialRating' => 'required|integer|min:1|max:5',
        ]);

        $data = [
            'name' => $this->testimonialName,
            'role' => $this->testimonialRole,
            'content' => $this->testimonialContent,
            'rating' => $this->testimonialRating,
            'is_active' => $this->testimonialIsActive,
            'sort_order' => $this->testimonialSortOrder,
        ];

        if ($this->testimonialImage) {
            $data['image'] = $this->testimonialImage->store('testimonials', 'public');
        }

        if ($this->editingTestimonialId) {
            Testimonial::findOrFail($this->editingTestimonialId)->update($data);
        } else {
            Testimonial::create($data);
        }

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Testimonial berhasil disimpan!'
        ]);
        $this->closeTestimonialModal();
    }

    public function deleteTestimonial(int $id)
    {
        $t = Testimonial::findOrFail($id);
        if ($t->image) {
            Storage::disk('public')->delete($t->image);
        }
        $t->delete();
        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Testimonial berhasil dihapus!'
        ]);
    }

    public function confirmDelete(string $type, int $id)
    {
        $this->pendingDeleteType = $type;
        $this->pendingDeleteId = $id;
        $this->dispatch('showConfirm', [
            'title' => 'Konfirmasi Hapus',
            'message' => 'Apakah Anda yakin ingin menghapus data ini?',
            'confirmText' => 'Ya, Hapus',
            'confirmAction' => 'deleteConfirmed'
        ]);
    }

    public function deleteConfirmed()
    {
        if (!$this->pendingDeleteId || !$this->pendingDeleteType) {
            return;
        }

        match($this->pendingDeleteType) {
            'testimonial' => $this->deleteTestimonial($this->pendingDeleteId),
            'faq' => $this->deleteFaq($this->pendingDeleteId),
            'heroSlide' => $this->deleteHeroSlide($this->pendingDeleteId),
            'statistic' => $this->deleteStatistic($this->pendingDeleteId),
            'deliveryArea' => $this->deleteDeliveryArea($this->pendingDeleteId),
            default => null
        };

        $this->pendingDeleteId = null;
        $this->pendingDeleteType = '';
    }

    private function resetTestimonialForm()
    {
        $this->editingTestimonialId = null;
        $this->testimonialName = '';
        $this->testimonialRole = '';
        $this->testimonialContent = '';
        $this->testimonialRating = 5;
        $this->testimonialImage = null;
        $this->testimonialImagePath = null;
        $this->testimonialIsActive = true;
        $this->testimonialSortOrder = 0;
    }

    // ==================== FAQ CRUD ====================
    public function openFaqModal(?int $id = null)
    {
        if ($id) {
            $f = Faq::findOrFail($id);
            $this->editingFaqId = $f->id;
            $this->faqQuestion = $f->question;
            $this->faqAnswer = $f->answer;
            $this->faqCategory = $f->category;
            $this->faqIsActive = $f->is_active;
            $this->faqSortOrder = $f->sort_order;
        } else {
            $this->resetFaqForm();
        }
        $this->showFaqModal = true;
    }

    public function closeFaqModal()
    {
        $this->showFaqModal = false;
        $this->resetFaqForm();
    }

    public function saveFaq()
    {
        $this->validate([
            'faqQuestion' => 'required|string|max:500',
            'faqAnswer' => 'required|string',
            'faqCategory' => 'required|in:product,order,delivery,payment,other',
        ]);

        $data = [
            'question' => $this->faqQuestion,
            'answer' => $this->faqAnswer,
            'category' => $this->faqCategory,
            'is_active' => $this->faqIsActive,
            'sort_order' => $this->faqSortOrder,
        ];

        if ($this->editingFaqId) {
            Faq::findOrFail($this->editingFaqId)->update($data);
        } else {
            Faq::create($data);
        }

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'FAQ berhasil disimpan!'
        ]);
        $this->closeFaqModal();
    }

    public function deleteFaq(int $id)
    {
        Faq::findOrFail($id)->delete();
        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'FAQ berhasil dihapus!'
        ]);
    }

    private function resetFaqForm()
    {
        $this->editingFaqId = null;
        $this->faqQuestion = '';
        $this->faqAnswer = '';
        $this->faqCategory = 'product';
        $this->faqIsActive = true;
        $this->faqSortOrder = 0;
    }

    // ==================== HERO SLIDE CRUD ====================
    public function openHeroSlideModal(?int $id = null)
    {
        if ($id) {
            $s = HeroSlide::findOrFail($id);
            $this->editingHeroSlideId = $s->id;
            $this->heroSlideTitle = $s->title;
            $this->heroSlideSubtitle = $s->subtitle ?? '';
            $this->heroSlideImagePath = $s->image;
            $this->heroSlideButtonText = $s->button_text ?? '';
            $this->heroSlideButtonUrl = $s->button_url ?? '';
            $this->heroSlideButtonTarget = $s->button_target ?? '_self';
            $this->heroSlideSortOrder = $s->sort_order;
            $this->heroSlideIsActive = $s->is_active;
        } else {
            $this->resetHeroSlideForm();
        }
        $this->showHeroSlideModal = true;
    }

    public function closeHeroSlideModal()
    {
        $this->showHeroSlideModal = false;
        $this->resetHeroSlideForm();
    }

    public function saveHeroSlide()
    {
        $this->validate([
            'heroSlideTitle' => 'required|string|max:255',
            'heroSlideImage' => $this->editingHeroSlideId ? 'nullable' : 'required',
        ]);

        $data = [
            'title' => $this->heroSlideTitle,
            'subtitle' => $this->heroSlideSubtitle ?: null,
            'button_text' => $this->heroSlideButtonText ?: null,
            'button_url' => $this->heroSlideButtonUrl ?: null,
            'button_target' => $this->heroSlideButtonTarget,
            'sort_order' => $this->heroSlideSortOrder,
            'is_active' => $this->heroSlideIsActive,
        ];

        if ($this->heroSlideImage) {
            $data['image'] = $this->heroSlideImage->store('hero-slides', 'public');
        }

        if ($this->editingHeroSlideId) {
            HeroSlide::findOrFail($this->editingHeroSlideId)->update($data);
        } else {
            HeroSlide::create($data);
        }

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Hero Slide berhasil disimpan!'
        ]);
        $this->closeHeroSlideModal();
    }

    public function deleteHeroSlide(int $id)
    {
        $s = HeroSlide::findOrFail($id);
        if ($s->image) {
            Storage::disk('public')->delete($s->image);
        }
        $s->delete();
        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Hero Slide berhasil dihapus!'
        ]);
    }

    private function resetHeroSlideForm()
    {
        $this->editingHeroSlideId = null;
        $this->heroSlideTitle = '';
        $this->heroSlideSubtitle = '';
        $this->heroSlideImage = null;
        $this->heroSlideImagePath = null;
        $this->heroSlideButtonText = '';
        $this->heroSlideButtonUrl = '';
        $this->heroSlideButtonTarget = '_self';
        $this->heroSlideSortOrder = 0;
        $this->heroSlideIsActive = true;
    }

    // ==================== STATISTIC CRUD ====================
    public function openStatisticModal(?int $id = null)
    {
        if ($id) {
            $s = FarmStatistic::findOrFail($id);
            $this->editingStatisticId = $s->id;
            $this->statisticKey = $s->key;
            $this->statisticValue = $s->value;
            $this->statisticLabel = $s->label;
            $this->statisticIcon = $s->icon ?? '';
            $this->statisticSortOrder = $s->sort_order;
        } else {
            $this->resetStatisticForm();
        }
        $this->showStatisticModal = true;
    }

    public function closeStatisticModal()
    {
        $this->showStatisticModal = false;
        $this->resetStatisticForm();
    }

    public function saveStatistic()
    {
        $this->validate([
            'statisticKey' => 'required|string|max:100|unique:farm_statistics,key,' . ($this->editingStatisticId ?? 'NULL') . ',id',
            'statisticValue' => 'required|string|max:50',
            'statisticLabel' => 'required|string|max:255',
        ]);

        $data = [
            'key' => $this->statisticKey,
            'value' => $this->statisticValue,
            'label' => $this->statisticLabel,
            'icon' => $this->statisticIcon ?: null,
            'sort_order' => $this->statisticSortOrder,
        ];

        if ($this->editingStatisticId) {
            FarmStatistic::findOrFail($this->editingStatisticId)->update($data);
        } else {
            FarmStatistic::create($data);
        }

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Statistik berhasil disimpan!'
        ]);
        $this->closeStatisticModal();
    }

    public function deleteStatistic(int $id)
    {
        FarmStatistic::findOrFail($id)->delete();
        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Statistik berhasil dihapus!'
        ]);
    }

    private function resetStatisticForm()
    {
        $this->editingStatisticId = null;
        $this->statisticKey = '';
        $this->statisticValue = '';
        $this->statisticLabel = '';
        $this->statisticIcon = '';
        $this->statisticSortOrder = 0;
    }

    // ==================== DELIVERY AREA CRUD ====================
    public function openDeliveryAreaModal(?int $id = null)
    {
        if ($id) {
            $a = DeliveryArea::findOrFail($id);
            $this->editingDeliveryAreaId = $a->id;
            $this->deliveryAreaRegion = $a->region_name;
            $this->deliveryAreaCity = $a->city ?? '';
            $this->deliveryAreaProvince = $a->province ?? 'Sulawesi Utara';
            $this->deliveryAreaFee = (string) $a->fee;
            $this->deliveryAreaEstimatedDays = $a->estimated_days;
            $this->deliveryAreaIsActive = $a->is_active;
            $this->deliveryAreaSortOrder = $a->sort_order;
        } else {
            $this->resetDeliveryAreaForm();
        }
        $this->showDeliveryAreaModal = true;
    }

    public function closeDeliveryAreaModal()
    {
        $this->showDeliveryAreaModal = false;
        $this->resetDeliveryAreaForm();
    }

    public function saveDeliveryArea()
    {
        $this->validate([
            'deliveryAreaRegion' => 'required|string|max:255',
            'deliveryAreaFee' => 'required|numeric|min:0',
        ]);

        $data = [
            'region_name' => $this->deliveryAreaRegion,
            'city' => $this->deliveryAreaCity ?: null,
            'province' => $this->deliveryAreaProvince,
            'fee' => (float) $this->deliveryAreaFee,
            'estimated_days' => $this->deliveryAreaEstimatedDays,
            'is_active' => $this->deliveryAreaIsActive,
            'sort_order' => $this->deliveryAreaSortOrder,
        ];

        if ($this->editingDeliveryAreaId) {
            DeliveryArea::findOrFail($this->editingDeliveryAreaId)->update($data);
        } else {
            DeliveryArea::create($data);
        }

        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Area pengiriman berhasil disimpan!'
        ]);
        $this->closeDeliveryAreaModal();
    }

    public function deleteDeliveryArea(int $id)
    {
        DeliveryArea::findOrFail($id)->delete();
        $this->dispatch('showAlert', [
            'type' => 'success',
            'title' => 'Berhasil!',
            'message' => 'Area pengiriman berhasil dihapus!'
        ]);
    }

    private function resetDeliveryAreaForm()
    {
        $this->editingDeliveryAreaId = null;
        $this->deliveryAreaRegion = '';
        $this->deliveryAreaCity = '';
        $this->deliveryAreaProvince = 'Sulawesi Utara';
        $this->deliveryAreaFee = '0';
        $this->deliveryAreaEstimatedDays = 1;
        $this->deliveryAreaIsActive = true;
        $this->deliveryAreaSortOrder = 0;
    }

    // ==================== RENDER ====================
    public function render()
    {
        return view('livewire.admin.store-setting', [
            'testimonials' => Testimonial::ordered()->get(),
            'faqs' => Faq::ordered()->get(),
            'heroSlides' => HeroSlide::ordered()->get(),
            'statistics' => FarmStatistic::ordered()->get(),
            'deliveryAreas' => DeliveryArea::ordered()->get(),
        ]);
    }
}
