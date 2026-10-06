<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        Faq::query()->whereNull('faqable_type')->delete();
        foreach (require __DIR__.'/data/faqs.php' as $i => [$enQ, $enA, $arQ, $arA]) {
            Faq::query()->create([
                'question' => ['en' => $enQ, 'ar' => $arQ],
                'answer' => ['en' => $enA, 'ar' => $arA],
                'sort_order' => $i,
            ]);
        }

        foreach (require __DIR__.'/data/pages.php' as $key => $page) {
            Page::query()->updateOrCreate(['key' => $key], $page);
        }

        // Placeholders only — never public (scope "visible" + model guard), listed on the launch checklist.
        if (! Testimonial::query()->exists()) {
            foreach (range(1, 3) as $i) {
                Testimonial::query()->create([
                    'author_name' => "[placeholder] Client {$i}",
                    'author_role' => ['en' => '[placeholder] Role', 'ar' => '[placeholder] المنصب'],
                    'company' => ['en' => '[placeholder] Company', 'ar' => '[placeholder] الشركة'],
                    'quote' => ['en' => '[placeholder] Replace with a real quote the client approved.', 'ar' => '[placeholder] استبدل هذا باقتباس حقيقي وافق عليه العميل.'],
                    'is_placeholder' => true,
                    'is_published' => false,
                    'sort_order' => $i,
                ]);
            }
        }

        // From the earlier portfolio draft; unpublished until dates and details are confirmed.
        $experience = [
            ['Schemacode', ['en' => 'Full-stack developer', 'ar' => 'مطوّر Full-Stack'], ['en' => 'Laravel and Filament systems: admin panels, APIs and modern UI integrations.', 'ar' => 'أنظمة Laravel وFilament: لوحات إدارة وواجهات برمجية وتكامل مع واجهات حديثة.']],
            ['hossam-x-studios', ['en' => 'Web developer', 'ar' => 'مطوّر ويب'], ['en' => 'End-to-end web projects combining design sensibility with engineering.', 'ar' => 'مشاريع ويب متكاملة تجمع بين الحس التصميمي والهندسة البرمجية.']],
            ['Media Print', ['en' => 'Graphic designer → developer', 'ar' => 'مصمّم جرافيك ثم مطوّر'], ['en' => 'Print, digital and brand identity work — where the design-to-code path started.', 'ar' => 'أعمال طباعة ومحتوى رقمي وهويات بصرية، ومن هنا بدأ الطريق من التصميم إلى البرمجة.']],
        ];
        foreach ($experience as $i => [$company, $role, $description]) {
            Experience::query()->updateOrCreate(['company' => $company], [
                'role' => $role,
                'description' => $description,
                'is_published' => false,
                'sort_order' => $i,
            ]);
        }
    }
}
