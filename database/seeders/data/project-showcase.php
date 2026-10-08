<?php

/*
| Owner-approved public showcase (D-044), layered over the anonymized baseline in projects.php.
| Source: the owner's projects-portfolio.xlsx (reviewed 2026-10-07/08) plus facts verified earlier
| from code. The sheet's "verification limits" are respected: no claims of live payments, Google
| sign-in, self-service booking, carrier API tracking or production/delivery the code doesn't prove.
|
| Keys replace the baseline; 'reveal' sets the visibility toggles; 'extra_features' are appended to
| the baseline features; 'cover' is a mockup in database/seeders/media attached to the public
| "cover" collection.
*/

$ul = fn (array $items): string => '<ul>'.implode('', array_map(fn ($i) => "<li>{$i}</li>", $items)).'</ul>';
$ol = fn (array $items): string => '<ol>'.implode('', array_map(fn ($i) => "<li>{$i}</li>", $items)).'</ol>';

return [
    'b2b-export-platform' => [
        'sort_order' => 1,
        'is_featured' => true,
        'reveal' => ['client_name' => true, 'live_link' => true],
        'repo_url' => 'https://github.com/OmarKhaled001/petrogina',
        'cover' => 'covers/petrogina-trading.webp',
        'title' => ['en' => 'Petrogina Trading — agricultural export management platform', 'ar' => 'Petrogina Trading — منصة لإدارة طلبات التصدير الزراعي'],
        'summary' => [
            'en' => 'A B2B platform that connects the catalogue of Egyptian agricultural products with structured quote requests, customer accounts and order and shipment follow-up — in English and Arabic.',
            'ar' => 'منصة تجارة بين الشركات تربط عرض المنتجات الزراعية المصرية بطلبات التسعير وحسابات العملاء ومتابعة الطلبات والشحنات، بالعربية والإنجليزية.',
        ],
        'goals' => [
            'en' => $ul(['Introduce buyers to each product and its specifications.', 'Collect pricing, packing and destination requirements in one structured request.', 'Support order and shipment follow-up for customers and staff.', 'Organise the company’s content, documents and communication.']),
            'ar' => $ul(['تعريف المشترين بالمنتجات ومواصفاتها.', 'جمع متطلبات التسعير والتعبئة والوجهة في طلب واحد منظّم.', 'دعم متابعة الطلبات والشحنات للعملاء والفريق.', 'تنظيم محتوى الشركة ووثائقها والتواصل معها.']),
        ],
        'audience' => [
            'en' => '<p>Importers, distributors and trade buyers sourcing Egyptian produce — and the exporter’s own sales, export and management teams who answer them.</p>',
            'ar' => '<p>المستوردون والموزعون والمشترون التجاريون الباحثون عن المنتجات الزراعية المصرية، إلى جانب فرق المبيعات والتصدير وإدارة الشركة التي تتابع طلباتهم.</p>',
        ],
        'solution' => [
            'en' => '<p>The platform follows a trade buyer from first look to shipment: a fast bilingual public site, an authenticated customer portal and a Filament back-office with role-based permissions across 20 modules.</p><ul><li>Buyers browse a catalogue with spec sheets, a seasons calendar and sourcing regions, then send one quote request covering several products with quantities, packing, destination port and shipping terms.</li><li>Customers follow their orders and notifications in the portal, and a tracking page shows the shipment information the team has recorded, with links to the carriers’ own tracking.</li><li>Staff manage products (including bulk Excel imports), quotes, orders, shipments, quality documents, the company profile, careers and SEO from one panel.</li><li>Two AI assistants — one for staff, one for customers — answer questions through server-side tools that check permissions on every call.</li></ul>',
            'ar' => '<p>ترافق المنصة المشتري التجاري من أول تصفّح حتى الشحن: موقع عام سريع ثنائي اللغة، وبوابة عملاء تتطلب تسجيل الدخول، ولوحة Filament بصلاحيات حسب الدور تمتد عبر 20 وحدة.</p><ul><li>يتصفّح المشترون كتالوجًا بمواصفات المنتجات وتقويم المواسم ومناطق التوريد، ثم يرسلون طلب تسعير واحدًا يشمل عدة منتجات بالكميات والتعبئة وميناء الوجهة وشروط الشحن.</li><li>يتابع العملاء طلباتهم وإشعاراتهم من البوابة، وتعرض صفحة التتبّع معلومات الشحنة التي يسجّلها الفريق مع روابط إلى صفحات التتبّع لدى شركات الشحن.</li><li>يدير الموظفون المنتجات (بما في ذلك الاستيراد الجماعي من Excel) وعروض الأسعار والطلبات والشحنات ووثائق الجودة وملف الشركة والوظائف وتحسين محركات البحث من لوحة واحدة.</li><li>مساعدان بالذكاء الاصطناعي، أحدهما للموظفين والآخر للعملاء، يجيبان عن الأسئلة عبر أدوات على الخادم تتحقّق من الصلاحيات في كل استدعاء.</li></ul>',
        ],
        'journey' => [
            'en' => $ol(['Browse a product and its specifications.', 'Create an account and send a quote request with quantities, packing and destination.', 'Follow orders and notifications from the account, and open the tracking page for the shipment information available.', 'The export team receives the request, replies and updates its status from the back-office.']),
            'ar' => $ol(['استعرض المنتج ومواصفاته.', 'أنشئ حسابًا وأرسل طلب التسعير بالكميات والتعبئة والوجهة.', 'تابع الطلبات والإشعارات من حسابك، واستخدم صفحة التتبّع للاطلاع على معلومات الشحنة المتاحة.', 'يستقبل فريق التصدير الطلب ويرد عليه ويحدّث حالته من لوحة الإدارة.']),
        ],
        'extra_features' => [
            ['Multi-product quote requests', 'One request covers several products with quantities, packing, destination port and shipping terms.', 'طلبات تسعير لعدة منتجات', 'طلب واحد يشمل عدة منتجات بالكميات والتعبئة وميناء الوجهة وشروط الشحن.'],
            ['Quality documents & company profile', 'Quality certificates, a company profile and a catalogue visitors can view and download.', 'وثائق الجودة وملف الشركة', 'شهادات الجودة وملف تعريفي للشركة وكتالوج يمكن للزوار عرضه وتنزيله.'],
        ],
    ],

    'travel-booking-platform' => [
        'sort_order' => 2,
        'is_featured' => true,
        'reveal' => ['client_name' => true, 'live_link' => true],
        'repo_url' => 'https://github.com/OmarKhaled001/cairokey',
        'cover' => 'covers/cairokey.webp',
        'client_name' => ['en' => 'CairoKey', 'ar' => 'كايرو كي'],
        'client_aliases' => ['CairoKey', 'Cairo Key', 'cairokey', 'كايرو كي'],
        'title' => ['en' => 'CairoKey — a platform for exploring stays and transport in Egypt', 'ar' => 'CairoKey — منصة لاستكشاف الإقامة والتنقل في مصر'],
        'summary' => [
            'en' => 'A bilingual site that brings furnished apartments, hotels, cars, travel services and offers in Egypt into one place, with unified search, detail pages and a brand identity designed alongside the product.',
            'ar' => 'موقع يجمع خيارات الإقامة والتنقل والخدمات السياحية في مصر، مع البحث وتفاصيل الخدمات ودعم العربية والإنجليزية، وهوية بصرية صُمّمت مع المنتج.',
        ],
        'role' => ['en' => 'Designer and full-stack developer — brand identity, UI and platform', 'ar' => 'مصمّم ومطوّر Full-Stack — الهوية البصرية والواجهة والمنصة'],
        'challenge' => [
            'en' => '<p>The company offers very different services — furnished apartments, hotels, car rental, airport services and seasonal offers. Visitors needed one trustworthy place to discover them in Arabic or English and reach the team, and the team needed one place to manage every listing and request.</p>',
            'ar' => '<p>تقدّم الشركة خدمات مختلفة تمامًا: شققًا مفروشة وفنادق وتأجير سيارات وخدمات مطار وعروضًا موسمية. احتاج الزوار إلى مكان واحد موثوق لاكتشافها بالعربية أو الإنجليزية والتواصل مع الفريق، واحتاج الفريق إلى مكان واحد لإدارة كل العروض والطلبات.</p>',
        ],
        'goals' => [
            'en' => $ul(['Bring every travel need into one place.', 'Make it quick to search and reach the details of each service.', 'Encourage inquiries and booking requests.', 'Manage content and bookings from the back-office.']),
            'ar' => $ul(['تجميع احتياجات الرحلة في مكان واحد.', 'تسهيل البحث والوصول إلى تفاصيل الخدمات.', 'تشجيع الاستفسارات وطلبات الحجز.', 'إدارة المحتوى والحجوزات من لوحة الإدارة.']),
        ],
        'audience' => [
            'en' => '<p>Tourists and visitors to Egypt, and individuals and families looking for accommodation, transport and travel services.</p>',
            'ar' => '<p>السائحون وزوار مصر، والأفراد والعائلات الباحثون عن إقامة وتنقّل وخدمات سفر.</p>',
        ],
        'solution' => [
            'en' => '<p>A Laravel site with dedicated listing and detail pages for apartments, hotels, cars, services and offers, a unified keyword search across all of them, featured options on the home page, and customer registration.</p><p>Behind it, a Filament back-office manages listings, translated content, clients and booking records — validated so the same unit can’t be booked for overlapping dates — with a bookings chart and live stats. The visual identity, from the Arabic wordmark and logo system to business cards and launch materials, was designed alongside the product so the brand and the interface speak the same language.</p>',
            'ar' => '<p>موقع Laravel بصفحات عرض وتفاصيل مخصّصة للشقق والفنادق والسيارات والخدمات والعروض، وبحث موحّد قائم على الكلمات المفتاحية عبرها جميعًا، وخيارات مميزة في الصفحة الرئيسية، وتسجيل للعملاء.</p><p>وخلفه لوحة Filament لإدارة العروض والمحتوى المترجم والعملاء وسجلات الحجوزات، مع التحقّق من عدم حجز الوحدة نفسها في مواعيد متداخلة، ورسم بياني للحجوزات وإحصاءات مباشرة. وقد صُمّمت الهوية البصرية، من الشعار العربي ونظام الشعارات إلى بطاقات العمل ومواد الإطلاق، بالتوازي مع المنتج لتتحدّث العلامة والواجهة اللغة نفسها.</p>',
        ],
        'journey' => [
            'en' => $ol(['Browse a category or search for a service.', 'Open the details of an apartment, hotel, car or offer.', 'Contact the team to complete the request.', 'Admins manage content and records from the back-office.']),
            'ar' => $ol(['تصفّح الفئة أو ابحث عن الخدمة.', 'افتح تفاصيل الشقة أو الفندق أو السيارة أو العرض.', 'تواصل مع الفريق لاستكمال الطلب.', 'يدير المسؤول المحتوى والسجلات عبر لوحة الإدارة.']),
        ],
        'features' => [
            ['One platform, five service types', 'Apartments, hotels, cars, services and offers, each with its own listing and detail pages.', 'منصة واحدة لخمسة أنواع من الخدمات', 'شقق وفنادق وسيارات وخدمات وعروض، لكلٍّ منها صفحات عرض وتفاصيل خاصة.'],
            ['Unified search', 'Keyword-mapped search across every service type.', 'بحث موحّد', 'بحث قائم على الكلمات المفتاحية عبر كل أنواع الخدمات.'],
            ['Offers and featured options', 'Highlighted offers and picks on the home page and listings.', 'عروض وخيارات مميزة', 'عروض وخيارات بارزة في الصفحة الرئيسية وصفحات العرض.'],
            ['Arabic and English content', 'Translated listings and pages managed from the back-office.', 'محتوى بالعربية والإنجليزية', 'عروض وصفحات مترجمة تُدار من لوحة الإدارة.'],
            ['Booking records with overlap checks', 'Back-office bookings are validated against overlapping dates for the same unit.', 'سجلات حجوزات مع منع التداخل', 'يُتحقَّق من حجوزات لوحة الإدارة حتى لا تتداخل مواعيد الوحدة نفسها.'],
            ['Brand identity', 'Arabic wordmark, logo system and launch materials designed with the product.', 'هوية بصرية', 'شعار عربي ونظام شعارات ومواد إطلاق صُمّمت مع المنتج.'],
        ],
    ],

    'apparel-design-marketplace' => [
        'sort_order' => 3,
        'is_featured' => true,
        'reveal' => ['client_name' => true],
        'repo_url' => 'https://github.com/OmarKhaled001/narrva',
        'cover' => 'covers/narrva.webp',
        'title' => ['en' => 'NARRVA — an apparel store that turns stories into personal designs', 'ar' => 'NARRVA — متجر ملابس يحوّل القصص إلى تصميمات شخصية'],
        'summary' => [
            'en' => 'A bilingual store for ready-made apparel and custom designs: customers share their story, review and approve the design with the team, then follow the order from their account.',
            'ar' => 'متجر للملابس الجاهزة والتصميمات المخصّصة بالعربية والإنجليزية، يشارك فيه العميل قصته ويراجع التصميم مع الفريق ويعتمده، ثم يتابع طلبه من حسابه.',
        ],
        'goals' => [
            'en' => $ul(['Offer clothing with personal meaning.', 'Connect customers with the design team.', 'Organise design review and approval.', 'Make buying and order follow-up simple.']),
            'ar' => $ul(['تقديم ملابس ذات معنى شخصي.', 'ربط العميل بفريق التصميم.', 'تنظيم مراجعة التصميم واعتماده.', 'تسهيل الشراء ومتابعة الطلبات.']),
        ],
        'audience' => [
            'en' => '<p>People who want clothes that express their identity, and customers looking for a one-of-a-kind piece or a meaningful gift.</p>',
            'ar' => '<p>محبّو الملابس المعبّرة عن الهوية الشخصية، والعملاء الباحثون عن قطعة فريدة أو هدية ذات معنى.</p>',
        ],
        'solution' => [
            'en' => '<p>A Laravel storefront with locale-prefixed URLs, a cart and checkout, and a Filament back-office for admin, fulfilment and editor roles.</p><ul><li>Custom design requests move through a 14-stage workflow with designer chat, file attachments and versioned previews that the customer approves.</li><li>Published ideas become products; a commission service freezes each order item’s split at purchase time and tracks payouts.</li><li>Checkout reserves stock, and a scheduled job releases it when a payment isn’t completed.</li><li>Customers keep a wishlist, gift cards, approved reviews and a status history for every order.</li></ul>',
            'ar' => '<p>واجهة متجر بـ Laravel بروابط مسبوقة باللغة، وسلة وإتمام شراء، ولوحة Filament بأدوار للإدارة والتنفيذ والتحرير.</p><ul><li>تمر طلبات التصميم المخصّص بمسار من 14 مرحلة يشمل محادثة مع المصمّم ومرفقات ومعاينات بإصدارات متتالية يعتمدها العميل.</li><li>تتحوّل الأفكار المنشورة إلى منتجات، وتثبّت خدمة العمولات نصيب كل عنصر في الطلب لحظة الشراء وتتابع المستحقات.</li><li>يحجز إتمام الشراء المخزون، وتحرّره مهمة مجدولة عندما لا يكتمل الدفع.</li><li>يحتفظ العملاء بقائمة مفضلة وبطاقات هدايا ومراجعات معتمدة وسجل حالات لكل طلب.</li></ul>',
        ],
        'architecture' => [
            'en' => '<p>Laravel 13 and Filament 5 with separate customer and admin authentication guards, per-module/per-action permissions enforced by 25 policies, and 22 focused service classes. A hand-written Paymob client verifies payment webhooks with HMAC-SHA512 (the integration is in the code; production activation depends on the merchant account). Social sign-in is configurable from the dashboard with encrypted secrets. A read-only JSON API exposes ideas for future clients, and a React prototype with Playwright and axe accessibility tests explored the UX before the Laravel build.</p>',
            'ar' => '<p>‏Laravel 13 وFilament 5 مع حارسَي مصادقة منفصلين للعملاء والإدارة، وصلاحيات لكل وحدة ولكل إجراء تفرضها 25 سياسة، و22 صنف خدمة محدّد المهام. يتحقّق عميل Paymob مكتوب يدويًا من توقيع إشعارات الدفع عبر HMAC-SHA512 (التكامل موجود في الكود، وتفعيله في الإنتاج مرتبط بحساب التاجر). ويمكن ضبط تسجيل الدخول الاجتماعي من لوحة التحكم مع تشفير المفاتيح السرية. وتتيح واجهة JSON للقراءة فقط عرض الأفكار لتطبيقات مستقبلية، وقد سبق البناءَ نموذجٌ أولي بـ React مع اختبارات Playwright واختبارات وصول axe لاستكشاف تجربة المستخدم.</p>',
        ],
        'journey' => [
            'en' => $ol(['Browse ready-made pieces, add a choice to the cart and complete the order details.', 'Or create an account and start a custom design request, sharing the story behind it.', 'Talk to the team, review the previews and approve the design.', 'Follow every stage of the order from the account.']),
            'ar' => $ol(['تصفّح المنتجات الجاهزة وأضف اختيارك إلى السلة ثم أكمل بيانات الطلب.', 'أو أنشئ حسابًا وابدأ طلب تصميم مخصّص وشارك القصة التي يعبّر عنها.', 'تواصل مع الفريق وراجع المعاينات ثم اعتمد التصميم.', 'تابع مراحل الطلب من حسابك.']),
        ],
        'features' => [
            ['Custom design workflow', 'Requests, designer chat, attachments and versioned previews with approve or revise steps.', 'مسار التصميم المخصّص', 'طلبات ومحادثة مع المصمّم ومرفقات ومعاينات بإصدارات متتالية مع خطوات اعتماد أو تعديل.'],
            ['Ideas marketplace with commissions', 'Public ideas, referral links and commission payouts frozen per order item.', 'سوق الأفكار مع العمولات', 'أفكار منشورة وروابط إحالة ومستحقات عمولة مثبّتة لكل عنصر في الطلب.'],
            ['Paymob integration with stock reservation', 'Signed webhook verification and automatic release of unpaid reservations.', 'تكامل Paymob مع حجز المخزون', 'تحقّق من توقيع الإشعارات وتحرير تلقائي للحجوزات غير المدفوعة.'],
            ['Wishlist, gift cards and reviews', 'Customer accounts with a wishlist, printable gift cards and approved reviews.', 'المفضلة وبطاقات الهدايا والمراجعات', 'حسابات عملاء بقائمة مفضلة وبطاقات هدايا قابلة للطباعة ومراجعات معتمدة.'],
            ['Analytics dashboard', 'Sales, profit, visitors and top customers in 13 widgets.', 'لوحة إحصاءات', 'المبيعات والأرباح والزوار وأفضل العملاء في 13 مؤشرًا.'],
        ],
    ],

    'print-on-demand-platform' => [
        'sort_order' => 4,
        'is_published' => true,
        'is_featured' => true,
        'year' => 2025,
        'reveal' => ['client_name' => true, 'live_link' => true, 'repo_link' => true],
        'repo_url' => 'https://github.com/OmarKhaled001/printalia',
        'cover' => 'covers/printalia.webp',
        'title' => ['en' => 'Printalia — connecting design to on-demand production', 'ar' => 'Printalia — منصة تربط التصميم بالإنتاج عند الطلب'],
        'summary' => [
            'en' => 'An Arabic print-on-demand platform for creators in Yemen: product customisation, designer and factory order management, subscriptions and earnings in one system.',
            'ar' => 'منصة للطباعة عند الطلب تجمع تخصيص المنتجات وإدارة طلبات المصممين والمصانع والاشتراكات والأرباح في تجربة عربية، موجّهة إلى المبدعين في اليمن.',
        ],
        'industry' => ['en' => 'Print-on-demand e-commerce', 'ar' => 'الطباعة عند الطلب والتجارة الإلكترونية'],
        'role' => ['en' => 'Full-stack developer — platform, three Filament panels and design editor', 'ar' => 'مطوّر Full-Stack — المنصة ولوحات Filament الثلاث ومحرّر التصميم'],
        'challenge' => [
            'en' => '<p>Creators who want to launch their own brand usually hit the same wall: buying stock up front and coordinating production for every order. The platform had to let a designer customise products and sell under their own name, hand each customer order to a factory, and keep subscriptions, order states and the designer’s earnings in one place — in Arabic.</p>',
            'ar' => '<p>يصطدم المبدعون الراغبون في إطلاق علامتهم الخاصة غالبًا بالعائق نفسه: شراء المخزون مسبقًا وتنسيق الإنتاج لكل طلب. كان على المنصة أن تتيح للمصمّم تخصيص المنتجات وبيعها باسمه، وتسليم كل طلب عميل إلى مصنع، وجمع الاشتراكات وحالات الطلبات وأرباح المصمّم في مكان واحد، بالعربية.</p>',
        ],
        'goals' => [
            'en' => $ul(['Let creators launch products under their own brand without buying stock first.', 'Make product customisation easy.', 'Organise production orders between designer and factory.', 'Track subscriptions, earnings and referrals.']),
            'ar' => $ul(['تمكين المبدع من إطلاق منتجات بعلامته الخاصة دون شراء مخزون مسبق.', 'تسهيل تخصيص المنتجات.', 'تنظيم طلبات الإنتاج بين المصمّم والمصنع.', 'متابعة الاشتراكات والأرباح والإحالات.']),
        ],
        'audience' => [
            'en' => '<p>Designers, creators and emerging brands in Yemen — together with the factories that produce their orders and the team that runs the platform.</p>',
            'ar' => '<p>المصممون والمبدعون وأصحاب العلامات الناشئة في اليمن، إلى جانب المصانع التي تنفّذ طلباتهم وفريق إدارة المنصة.</p>',
        ],
        'solution' => [
            'en' => '<p>Printalia connects the creator, the product and the factory through three separate Filament panels — admin, designer and factory.</p><ul><li>A designer picks a subscription plan, uploads the payment receipt for admin review, then customises products in a design editor with live previews.</li><li>The designer records customer orders and sends them for production; the factory accepts or rejects each order, and a rejected order can be redirected to another factory.</li><li>Order statuses and acceptance or rejection notifications keep everyone informed, and the designer’s earnings are recorded as a transaction when production is completed.</li><li>Referral transactions are tracked, with the commission configurable by the admin.</li></ul>',
            'ar' => '<p>تربط Printalia بين المبدع والمنتج والمصنع عبر ثلاث لوحات Filament منفصلة للإدارة والمصمّم والمصنع.</p><ul><li>يختار المصمّم باقة الاشتراك ويرفع إيصال الدفع لتراجعه الإدارة، ثم يخصّص المنتجات في محرّر تصميم مع معاينات فورية.</li><li>يسجّل المصمّم طلبات عملائه ويرسلها للتنفيذ، ويقبل المصنع الطلب أو يرفضه، ويمكن إعادة توجيه الطلب المرفوض إلى مصنع آخر.</li><li>تُبقي حالات الطلب وإشعارات القبول والرفض الجميع على اطلاع، ويُسجَّل ربح المصمّم كمعاملة عند اكتمال التنفيذ.</li><li>تُتابَع معاملات الإحالة، مع عمولة تضبطها الإدارة.</li></ul>',
        ],
        'journey' => [
            'en' => $ol(['Subscribe and upload the payment receipt for review.', 'Customise a product and create the design.', 'Record the customer’s order and send it for production.', 'The factory handles the order; its status and notifications update, and the earnings are recorded on completion.']),
            'ar' => $ol(['اشترك وارفع إيصال الدفع للمراجعة.', 'خصّص المنتج وأنشئ التصميم.', 'سجّل طلب العميل ثم أرسله للتنفيذ.', 'يتعامل المصنع مع الطلب وتتحدّث حالته وإشعاراته، ويُسجَّل الربح عند اكتمال التنفيذ.']),
        ],
        'architecture' => [
            'en' => '<p>Laravel 12 with Filament 3 running three panels (admin, designer and factory) over 14 Eloquent models. Intervention Image renders product previews, database notifications keep designers and factories informed, and the interface is Arabic-first with a language switcher in the panels.</p>',
            'ar' => '<p>‏Laravel 12 مع Filament 3 بثلاث لوحات (الإدارة والمصمّم والمصنع) فوق 14 نموذج بيانات. تُنشئ مكتبة Intervention Image معاينات المنتجات، وتُبقي إشعارات قاعدة البيانات المصممين والمصانع على اطلاع، والواجهة عربية أولًا مع مبدّل للغة في اللوحات.</p>',
        ],
        'features' => [
            ['Design editor & product previews', 'Customise products and see the result before ordering.', 'محرّر تصميم ومعاينات للمنتجات', 'تخصيص المنتجات ومشاهدة النتيجة قبل الطلب.'],
            ['Admin, designer and factory panels', 'Three Filament panels, each scoped to its role.', 'لوحات للإدارة والمصمّم والمصنع', 'ثلاث لوحات Filament، لكلٍّ منها نطاق دوره.'],
            ['Customers, designs and orders', 'Designers manage their customers, designs and orders in one place.', 'العملاء والتصاميم والطلبات', 'يدير المصمّم عملاءه وتصاميمه وطلباته في مكان واحد.'],
            ['Subscriptions with receipt review', 'Plans paid by uploading a receipt that the admin approves.', 'اشتراكات مع مراجعة الإيصال', 'باقات تُدفع برفع إيصال تعتمده الإدارة.'],
            ['Order statuses & notifications', 'Acceptance, rejection and production updates for designers and factories.', 'حالات الطلب والإشعارات', 'تحديثات القبول والرفض والتنفيذ للمصممين والمصانع.'],
            ['Redirecting rejected orders', 'A rejected order can be reassigned to another factory.', 'إعادة توجيه الطلبات المرفوضة', 'يمكن إسناد الطلب المرفوض إلى مصنع آخر.'],
            ['Earnings & referrals', 'Designer earnings and referral transactions recorded automatically.', 'الأرباح والإحالات', 'تسجيل أرباح المصمّم ومعاملات الإحالة تلقائيًا.'],
        ],
        'facts' => [
            ['3', 'Filament panels', 'لوحات Filament', 'app/Providers/Filament, GitHub 2026-10-09'],
            ['11', 'admin modules', 'وحدة إدارية', 'Filament resources across the 3 panels'],
            ['14', 'data models', 'نموذج بيانات', 'app/Models'],
        ],
        'categories' => ['ecommerce', 'saas'],
        'technologies' => ['laravel', 'filament', 'php', 'javascript'],
        'services' => ['laravel-development', 'ecommerce-development', 'filament-admin-panels', 'saas-development'],
    ],

    'multilingual-corporate-cms' => [
        'sort_order' => 5,
        'is_featured' => true,
        'reveal' => ['client_name' => true, 'live_link' => true],
        'live_url' => 'https://ludicuae.com',
        'repo_url' => 'https://github.com/OmarKhaled001/Ludic',
        'cover' => 'covers/ludic.webp',
        'title' => ['en' => 'Ludic — a digital catalogue for food products and ingredients', 'ar' => 'Ludic — كتالوج رقمي للمنتجات والمكونات الغذائية'],
        'summary' => [
            'en' => 'A multilingual website for a UAE food-ingredients supplier: product categories with details, company information and branches, and an inquiry form — all managed from a Filament CMS.',
            'ar' => 'موقع متعدد اللغات لشركة توريد غذائي في الإمارات، يعرض المنتجات في فئات منظمة مع تفاصيلها ومعلومات الشركة وفروعها ونموذج تواصل، ويُدار بالكامل من نظام إدارة محتوى مبني بـ Filament.',
        ],
        'goals' => [
            'en' => $ul(['Present the products in a clear, visual way.', 'Organise categories and product details.', 'Reach buyers in several languages.', 'Introduce the company, receive inquiries and keep content up to date.']),
            'ar' => $ul(['تقديم المنتجات بطريقة بصرية واضحة.', 'تنظيم الفئات والتفاصيل.', 'الوصول إلى جمهور متعدد اللغات.', 'التعريف بالشركة واستقبال الاستفسارات وإدارة المحتوى.']),
        ],
        'audience' => [
            'en' => '<p>Distributors and trade buyers — roasteries, food stores and confectionery makers — sourcing nuts, green coffee, dried fruits, spices and confectionery ingredients.</p>',
            'ar' => '<p>الموزعون والمشترون التجاريون، من المحامص ومتاجر الأغذية إلى مصنّعي الحلويات، الباحثون عن المكسرات والقهوة الخضراء والفواكه المجففة والتوابل وخامات الحلويات.</p>',
        ],
        'solution' => [
            'en' => '<p>A Laravel site with a Filament CMS where categories link to their products, and every page, product, branch and client logo is translatable into all six languages with its own SEO title, keywords and description.</p><p>Visitors move from categories to product details, learn about the company, its branches (on embedded maps), clients and testimonials, and send an inquiry that is saved for the team to follow up. A visit-tracking middleware feeds simple traffic widgets in the dashboard.</p>',
            'ar' => '<p>موقع Laravel مع نظام إدارة محتوى بـ Filament ترتبط فيه الفئات بمنتجاتها، وتُترجَم فيه كل صفحة ومنتج وفرع وشعار عميل إلى اللغات الست، مع عنوان SEO وكلمات مفتاحية ووصف مستقل لكل لغة.</p><p>ينتقل الزائر من الفئات إلى تفاصيل المنتجات، ويتعرّف على الشركة وفروعها (على خرائط مضمّنة) وعملائها وآرائهم، ثم يرسل استفسارًا يُحفظ ليتابعه الفريق. ويغذّي برنامج وسيط لتتبّع الزيارات مؤشرات بسيطة لحركة الزوار في لوحة التحكم.</p>',
        ],
        'journey' => [
            'en' => $ol(['Browse the product categories.', 'Open a category to see its products and details.', 'Send an inquiry through the contact form.', 'The team manages content, products and messages from the back-office.']),
            'ar' => $ol(['استعرض فئات المنتجات.', 'افتح الفئة للتعرّف على منتجاتها وتفاصيلها.', 'أرسل استفسارك من نموذج التواصل.', 'يدير الفريق المحتوى والمنتجات والرسائل من لوحة الإدارة.']),
        ],
        'extra_features' => [
            ['Visual product catalogue', 'Categories linked to products with managed images and details.', 'كتالوج منتجات بصري', 'فئات مرتبطة بمنتجاتها مع صور وتفاصيل قابلة للإدارة.'],
            ['Branches, clients & testimonials', 'Company pages with branches, client logos and customer testimonials; inquiries are stored for follow-up.', 'الفروع والعملاء والآراء', 'صفحات للشركة بفروعها وشعارات عملائها وآرائهم، مع حفظ الاستفسارات للمتابعة.'],
        ],
    ],

    // The remaining published case studies follow the five showcased ones.
    'dental-scan-saas' => ['sort_order' => 6],
    'client-operations-panel' => ['sort_order' => 7],
];
