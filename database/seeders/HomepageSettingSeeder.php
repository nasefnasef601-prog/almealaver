<?php

namespace Database\Seeders;

use App\Models\HomepageSetting;
use Illuminate\Database\Seeder;

class HomepageSettingSeeder extends Seeder
{
    public function run(): void
    {
        HomepageSetting::create([
            'key' => 'default',
            'is_active' => true,
            'data' => [
                'hero' => [
                    'badgeText' => 'المنصة الأولى للقدرات والتحصيلي',
                    'titlePrefix' => 'حقق',
                    'titleHighlight' => 'المئة',
                    'titleSuffix' => 'في اختباراتك',
                    'description' => 'رحلة تعليمية ذكية تجمع بين التدريب المكثف، الشروحات التفاعلية، والتحليل الدقيق لنقاط ضعفك لضمان أعلى الدرجات.',
                    'primaryCtaLabel' => 'ابدأ التدريب مجانًا',
                    'primaryCtaLink' => '/register',
                    'secondaryCtaLabel' => 'تصفح الدورات',
                    'secondaryCtaLink' => '/courses',
                    'imageUrl' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=600&h=600&fit=crop',
                    'imageAlt' => 'طالب يستخدم منصة المئة',
                    'floatingCardTitle' => 'منصة المئة',
                    'floatingCardSubtitle' => 'مستواك: متقدم',
                    'floatingCardProgressLabel' => 'التقدم',
                    'floatingCardProgressValue' => '75%',
                ],
                'stats' => [
                    ['id' => 'students', 'label' => 'طالب وطالبة', 'displayValue' => '150,000+'],
                    ['id' => 'courses', 'label' => 'دورة تدريبية', 'displayValue' => '500+'],
                    ['id' => 'assets', 'label' => 'مادة تعليمية', 'displayValue' => '50,000+'],
                    ['id' => 'rating', 'label' => 'تقييم عام', 'displayValue' => '4.8'],
                ],
                'whyChoose' => [
                    'title' => 'لماذا يختار الطلاب منصة المئة؟',
                    'description' => 'نحن لا نقدم مجرد دورات، بل نقدم نظامًا تعليميًا متكاملًا يساعدك على الفهم العميق، التدريب المستمر، وتحليل الأداء بطريقة بسيطة وفعالة.',
                    'features' => [
                        ['icon' => 'video', 'title' => 'شرح مباشر وتفاعلي', 'description' => 'احضر الحصص وتابع الشرح بخطوات منظمة تناسب مستواك.'],
                        ['icon' => 'users', 'title' => 'نخبة المعلمين', 'description' => 'معلمون ومتخصصون في القدرات والتحصيلي بخبرة عملية وأكاديمية.'],
                        ['icon' => 'chart', 'title' => 'تحليل الأداء', 'description' => 'تقارير دقيقة توضح نقاط قوتك وضعفك لتعرف أين تبدأ.'],
                        ['icon' => 'book', 'title' => 'ملفات ومراجعات', 'description' => 'ملخصات ومراجعات داعمة تساعدك قبل الاختبار وبعد التدريب.'],
                    ],
                    'bullets' => [
                        'تحديثات مستمرة للأسئلة والتدريب',
                        'مسارات تأسيس وتدريب ومراجعة في مكان واحد',
                        'دعم فني وأكاديمي متواصل',
                    ],
                ],
                'testimonials' => [
                    [
                        'id' => 't1',
                        'name' => 'سارة العتيبي',
                        'degree' => '98% قدرات',
                        'text' => 'المنصة غيرت طريقة مذاكرتي تمامًا. تحليل نقاط الضعف ساعدني أركز جهدي في المكان الصح.',
                        'image' => 'https://i.pravatar.cc/100?img=5',
                    ],
                    [
                        'id' => 't2',
                        'name' => 'فهد الشمري',
                        'degree' => '96% تحصيلي',
                        'text' => 'الشروحات والتدريبات كانت مرتبة جدًا وواضحة، وحسيت فعلًا أن عندي خطة كاملة وليست مجرد دروس.',
                        'image' => 'https://i.pravatar.cc/100?img=11',
                    ],
                    [
                        'id' => 't3',
                        'name' => 'نورة السالم',
                        'degree' => '99% قدرات',
                        'text' => 'الاختبارات المحاكية كانت قريبة جدًا من الاختبار الحقيقي، وهذا رفع ثقتي قبل يوم الاختبار.',
                        'image' => 'https://i.pravatar.cc/100?img=9',
                    ],
                ],
                'sections' => [
                    'featuredCoursesTitle' => 'الدورات الأكثر طلبًا',
                    'featuredCoursesSubtitle' => 'اختر دورتك وابدأ رحلة التفوق اليوم',
                    'whyChooseTitle' => 'لماذا يختار الطلاب منصة المئة؟',
                    'testimonialsTitle' => 'قصص نجاح نعتز بها',
                    'testimonialsSubtitle' => 'انضم لآلاف الطلاب الذين حققوا أحلامهم معنا',
                ],
            ],
        ]);
    }
}
