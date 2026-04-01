# Bavly KYC — نظام إدارة اعرف عميلك (داخلي)

نظام Laravel 12 داخلي لإدارة نماذج KYC باللغة العربية وواجهة RTL بثيم أسود/ذهبي، مع صلاحيات **مدير** و**موظف**، وجداول تحليلات، وتصدير Excel. معدّ للنشر على استضافة مثل Hostinger (PHP 8.3+، MySQL).

## المتطلبات

- PHP 8.3+
- Composer 2.x
- MySQL 8+
- Node.js 20+ (لبناء الأصول فقط)

## الإعداد المحلي

### أسرع طريقة (SQLite — بدون MySQL)

يتطلب **PHP 8.3+** و **Composer** و **Node** على جهازك.

```bash
cp .env.sqlite.example .env
bash scripts/run-local.sh
```

يفتح الخادم على `http://127.0.0.1:8000`. تسجيل الدخول: `superadmin` وكلمة المرور من `DatabaseSeeder` أو `SEED_ADMIN_PASSWORD` في `.env`.

### MySQL محليًا (مُوصى للتطابق مع الإنتاج)

دليل كامل: **[docs/LOCAL_MYSQL_MACOS.md](docs/LOCAL_MYSQL_MACOS.md)**

```bash
brew install mysql php composer   # قد يستغرق وقتًا على macOS قديم
brew services start mysql
mysql -u root -p < scripts/mysql-init.sql
cp .env.mysql.example .env
bash scripts/run-local-mysql.sh
```

### طريقة يدوية (MySQL أو SQLite)

1) تثبيت الحزم:

```bash
composer install
npm install
```

2) البيئة:

```bash
cp .env.example .env
php artisan key:generate
```

للتطوير السريع بـ SQLite: انسخ `.env.sqlite.example` إلى `.env` وأنشئ الملف `database/database.sqlite` (فارغ).

ثم عيّن اتصال MySQL في `.env` (`DB_*`)، ويفضّل:

- `APP_DEBUG=false` عند أي بيئة غير التطوير
- `APP_URL` يطابق نطاقك (مثلاً `https://kyc.example.com`)
- `SEED_ADMIN_PASSWORD` لكلمة مرور قوية لمستخدم البذرة قبل `db:seed` (اختياري؛ وإلا تُستخدم القيمة الافتراضية للمرة الأولى فقط في بيئة تطوير)

3) قاعدة البيانات والجلسات:

```bash
php artisan migrate --force
php artisan db:seed
```

4) بناء الواجهة:

```bash
npm run build
```

أو أثناء التطوير:

```bash
composer run dev
```

5) الدخول الافتراضي بعد البذرة:

- مستخدم: `superadmin`
- كلمة المرور: قيمة `SEED_ADMIN_PASSWORD` إن وُجدت، أو الافتراضي الموثّق في الـ Seeder للتشغيل الأولي (غيّره فورًا في الإنتاج).

تم إنشاء موظفين تجريبيين: `ahmed.employee` و `sara.employee` (كلمة المرور الافتراضية في `DatabaseSeeder` — غيّرها فورًا).

## الميزات الرئيسية

- تسجيل دخول بـ **اسم مستخدم** وكلمة مرور، جلسات آمنة، حدّ لمحاولات الدخول، وتذكّرني.
- **نسيان كلمة المرور**: صفحة إرشادية؛ الإعادة عبر المشرف مع فرض تغيير كلمة المرور عند أول دخول.
- نموذج KYC كامل بالعربية مع منطق شرطي في الواجهة (Alpine) وطلبات التحقق (FormRequest) وقواعد `prohibited/required` في الخادم.
- تعيين **المكلّف بالمتابعة** تلقائيًا: بافلي → أحمد الشيخ، ترانس روفر → محمود الشيخ، أخرى → فارغ.
- RBAC: موظف يمكن تقييده لرؤية سجلاته فقط أو كل السجلات؛ وصلاحية تقارير اختيارية للموظف.
- لوحة تقارير مع Chart.js (اتجاهات، أنواع خدمة، حالات، قمع، موظفون، تفكيكات ديموغرافية) مع مرشحات التاريخ/الموظف/الخدمة (الموظف يرى إحصاءاته فقط).
- تصدير Excel للمدير فقط (Maatwebsite/Excel).
- سجل نشاط بسيط، وسجل إعادة تعيين كلمات المرور، ومحاولات فاشلة للدخول.

## الأمان (ملخّص)

- كلمات مرور مشفّرة (Bcrypt)، سياسة كلمات مرور قوية (12+ مع تنويع).
- CSRF، سياسات تفويض للسجلات، تحقق من الصلاحية على العمليات الحساسة.
- ترويسات أمان أساسية، `APP_DEBUG=false` في الإنتاج، جلسات قاعدة بيانات، `TrustProxies` مضبوط عبر `TRUSTED_PROXIES`.
- طبقة خدمات للـ KYC والمستخدمين، ربط نموذجي آمن لـ `{kyc}` يعيد 404 عند عدم الصلاحية، وحدّ لزوم على تسجيل الدخول والتصدير والتقارير ولوحة المشرف.

راجع **[docs/REFACTOR_SECURITY_PERFORMANCE.md](docs/REFACTOR_SECURITY_PERFORMANCE.md)** لخطة إعادة الهيكلة، قوائم التحقق الأمنية والأداء، وملاحظات الإنتاج.

## النشر على Hostinger (إرشادات)

1. أنشئ قاعدة MySQL من لوحة Hostinger وصدّر القيم إلى `.env`.
2. ارفع الملفات (أو Git deploy) إلى المجلد العام للدومين؛ اجعل جذر الويب يشير إلى مجلد `public` (أو عدّل document root في الإعدادات).
3. على الخادم:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm ci
npm run build
```

4. تأكد من صلاحيات الكتابة على `storage/` و `bootstrap/cache/`.
5. عيّن `APP_ENV=production` و `APP_DEBUG=false` و `APP_URL` الصحيح.
6. فعّل HTTPS في Hostinger واجعل `SESSION_SECURE_COOKIE=true` في `.env` عند استخدام SSL.
7. غيّر كلمات المرور الافتراضية وأعد توليد مفاتيح الجلسة إن لزم (`php artisan key:generate` مرة واحدة عند أول نشر ثم ثبّت المفتاح).

## أوامر مفيدة

```bash
php artisan test
php artisan pint
```

## قائمة تحقق سريعة قبل الإطلاق

- [ ] بيانات `.env` ي production صحيحة و `APP_DEBUG=false`
- [ ] كلمات مرور `superadmin` والموظفين التجريبيين مُغيّرة أو حُذف الحسابات التجريبية
- [ ] HTTPS + `SESSION_SECURE_COOKIE`
- [ ] النسخ الاحتياطي لقاعدة البيانات مجدول
- [ ] التحقق من صلاحيات الملفات على `storage` و `bootstrap/cache`
- [ ] تجربة: دخول، إنشاء KYC، تصفية، تعديل، حذف ناعم، تصدير (مدير)، تقارير (مدير/موظف مسموح)، إعادة تعيين كلمة مرور، حساب موقوف

## الرخصة

هيكل المشروع مبني على Laravel (MIT). الكود الخاص بالمشروع يمكنك ترخيصه حسب احتياج مؤسستك.
