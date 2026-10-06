<?php

namespace Database\Seeders;

use App\Enums\ServiceItemKind;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (require __DIR__.'/data/services.php' as $order => $data) {
            $service = Service::query()->updateOrCreate(['slug' => $data['slug']], [
                'icon' => $data['icon'],
                'title' => $data['title'],
                'card_summary' => $data['card_summary'],
                'headline' => $data['headline'],
                'intro' => $data['intro'],
                'problem' => $data['problem'],
                'cta_text' => $data['cta_text'],
                'meta_title' => $data['meta_title'],
                'meta_description' => $data['meta_description'],
                'is_published' => true,
                'sort_order' => $order + 1,
            ]);

            $service->items()->delete();
            foreach ([ServiceItemKind::Deliverable->value => $data['deliverables'], ServiceItemKind::ProcessStep->value => $data['process']] as $kind => $items) {
                foreach ($items as $i => [$enTitle, $enBody, $arTitle, $arBody]) {
                    $service->items()->create([
                        'kind' => $kind,
                        'title' => ['en' => $enTitle, 'ar' => $arTitle],
                        'body' => ['en' => $enBody, 'ar' => $arBody],
                        'sort_order' => $i,
                    ]);
                }
            }

            $service->faqs()->delete();
            foreach ($data['faqs'] as $i => [$enQ, $enA, $arQ, $arA]) {
                $service->faqs()->create([
                    'question' => ['en' => $enQ, 'ar' => $arQ],
                    'answer' => ['en' => $enA, 'ar' => $arA],
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
