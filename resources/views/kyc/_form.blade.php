@php
    /** @var \App\Models\KycRecord $record */
    use App\Support\KycOptions;
@endphp

<div
    x-data="{
        bank: @js(old('has_bank_statement', $record->has_bank_statement)),
        married: @js(old('marital_status', $record->marital_status)),
        nat: @js(old('nationality_type', $record->nationality_type)),
        rejected: @js(old('previous_rejected', $record->previous_rejected)),
        visas: @js(old('has_previous_visas', $record->has_previous_visas)),
        service: @js(old('service_type', $record->service_type)),
        assignFor(s) {
            if (s === 'بافلي') return 'أحمد الشيخ';
            if (s === 'ترانس روفر') return 'محمود الشيخ';
            return '';
        },
    }"
    class="space-y-10"
>
    <input type="hidden" name="assigned_to" x-bind:value="assignFor(service)">

    <section class="card space-y-4">
        <h3 class="border-b border-zinc-800 pb-2 text-sm font-semibold uppercase tracking-wider text-[#d4af37]">أ — بيانات أساسية</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="label-dark">اسم الموظف</label>
                <input name="employee_name" type="text" class="input-dark" value="{{ old('employee_name', $record->employee_name) }}" required>
            </div>
            <div>
                <label class="label-dark">الاسم الكامل للعميل</label>
                <input name="client_full_name" type="text" class="input-dark" value="{{ old('client_full_name', $record->client_full_name) }}" required>
            </div>
            <div>
                <label class="label-dark">العمر</label>
                <input name="age" type="number" min="16" max="120" class="input-dark" value="{{ old('age', $record->age) }}" required>
            </div>
            <div>
                <label class="label-dark">المسمى في جواز السفر</label>
                <input name="passport_job_title" type="text" class="input-dark" value="{{ old('passport_job_title', $record->passport_job_title) }}">
            </div>
            <div>
                <label class="label-dark">مسمى وظيفي آخر</label>
                <input name="other_job_title" type="text" class="input-dark" value="{{ old('other_job_title', $record->other_job_title) }}">
            </div>
            <div>
                <label class="label-dark">نوع الخدمة</label>
                <select name="service_type" x-model="service" class="input-dark" required>
                    @foreach (KycOptions::SERVICE_TYPES as $st)
                        <option value="{{ $st }}">{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-dark">مكلّف بمتابعة الخدمة</label>
                <input type="text" class="input-dark" readonly x-bind:value="assignFor(service)" placeholder="يُحدّد تلقائيًا">
            </div>
        </div>
    </section>

    <section class="card space-y-4">
        <h3 class="border-b border-zinc-800 pb-2 text-sm font-semibold uppercase tracking-wider text-[#d4af37]">ب — مالية واجتماعية</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="label-dark">هل يوجد كشف حساب بنكي؟</label>
                <select name="has_bank_statement" x-model="bank" class="input-dark" required>
                    @foreach (KycOptions::YES_NO as $yn)
                        <option value="{{ $yn }}">{{ $yn }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="bank === 'نعم'" x-cloak>
                <label class="label-dark">الرصيد المتاح</label>
                <input name="available_balance" type="number" step="0.01" min="0" class="input-dark" :required="bank === 'نعم'" :disabled="bank !== 'نعم'" value="{{ old('available_balance', $record->available_balance) }}">
            </div>
            <div x-show="bank === 'لا'" x-cloak>
                <label class="label-dark">الرصيد المتوقع</label>
                <input name="expected_balance" type="number" step="0.01" min="0" class="input-dark" :required="bank === 'لا'" :disabled="bank !== 'لا'" value="{{ old('expected_balance', $record->expected_balance) }}">
            </div>
            <div>
                <label class="label-dark">الحالة الاجتماعية</label>
                <select name="marital_status" x-model="married" class="input-dark" required>
                    @foreach (KycOptions::MARITAL_STATUSES as $ms)
                        <option value="{{ $ms }}">{{ $ms }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="married === 'متزوج'" x-cloak>
                <label class="label-dark">عدد الأطفال</label>
                <input name="children_count" type="number" min="0" max="50" class="input-dark" :required="married === 'متزوج'" :disabled="married !== 'متزوج'" value="{{ old('children_count', $record->children_count) }}">
            </div>
            <div>
                <label class="label-dark">أقارب في الخارج؟</label>
                <select name="has_relatives_abroad" class="input-dark" required>
                    @foreach (KycOptions::YES_NO as $yn)
                        <option value="{{ $yn }}" @selected(old('has_relatives_abroad', $record->has_relatives_abroad) === $yn)>{{ $yn }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-dark">نوع الجنسية</label>
                <select name="nationality_type" x-model="nat" class="input-dark" required>
                    @foreach (KycOptions::NATIONALITY_TYPES as $nt)
                        <option value="{{ $nt }}">{{ $nt }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="nat === 'غير مصري'" x-cloak>
                <label class="label-dark">الجنسية</label>
                <input name="nationality" type="text" class="input-dark" :required="nat === 'غير مصري'" :disabled="nat !== 'غير مصري'" value="{{ old('nationality', $record->nationality) }}">
            </div>
            <div x-show="nat === 'غير مصري'" x-cloak>
                <label class="label-dark">حالة الإقامة</label>
                <input name="residency_status" type="text" class="input-dark" :required="nat === 'غير مصري'" :disabled="nat !== 'غير مصري'" value="{{ old('residency_status', $record->residency_status) }}">
            </div>
            <div>
                <label class="label-dark">المحافظة</label>
                <input name="governorate" type="text" class="input-dark" value="{{ old('governorate', $record->governorate) }}">
            </div>
        </div>
    </section>

    <section class="card space-y-4">
        <h3 class="border-b border-zinc-800 pb-2 text-sm font-semibold uppercase tracking-wider text-[#d4af37]">ج — تواصل واستشارة</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="label-dark">طريقة الاستشارة</label>
                <select name="consultation_method" class="input-dark" required>
                    @foreach (KycOptions::CONSULTATION_METHODS as $cm)
                        <option value="{{ $cm }}" @selected(old('consultation_method', $record->consultation_method) === $cm)>{{ $cm }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-dark">البريد الإلكتروني</label>
                <input name="email" type="email" class="input-dark" value="{{ old('email', $record->email) }}">
            </div>
            <div>
                <label class="label-dark">رقم الهاتف</label>
                <input name="phone_number" type="text" class="input-dark" value="{{ old('phone_number', $record->phone_number) }}" required>
            </div>
            <div>
                <label class="label-dark">واتساب</label>
                <input name="whatsapp_number" type="text" class="input-dark" value="{{ old('whatsapp_number', $record->whatsapp_number) }}">
            </div>
        </div>
    </section>

    <section class="card space-y-4">
        <h3 class="border-b border-zinc-800 pb-2 text-sm font-semibold uppercase tracking-wider text-[#d4af37]">د — التأشيرات ومحاور الرفض</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="label-dark">رفض تأشيرة سابقًا؟</label>
                <select name="previous_rejected" x-model="rejected" class="input-dark" required>
                    @foreach (KycOptions::YES_NO as $yn)
                        <option value="{{ $yn }}">{{ $yn }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="rejected === 'نعم'" x-cloak class="md:col-span-2 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="label-dark">عدد مرات الرفض / أرقام الملفات</label>
                    <input name="rejection_numbers" type="text" class="input-dark" :required="rejected === 'نعم'" :disabled="rejected !== 'نعم'" value="{{ old('rejection_numbers', $record->rejection_numbers) }}">
                </div>
                <div>
                    <label class="label-dark">بلد الرفض</label>
                    <input name="rejection_country" type="text" class="input-dark" :required="rejected === 'نعم'" :disabled="rejected !== 'نعم'" value="{{ old('rejection_country', $record->rejection_country) }}">
                </div>
                <div class="md:col-span-2">
                    <label class="label-dark">سبب الرفض</label>
                    <textarea name="rejection_reason" rows="3" class="input-dark" :required="rejected === 'نعم'" :disabled="rejected !== 'نعم'">{{ old('rejection_reason', $record->rejection_reason) }}</textarea>
                </div>
            </div>
            <div>
                <label class="label-dark">تأشيرات سابقة؟</label>
                <select name="has_previous_visas" x-model="visas" class="input-dark" required>
                    @foreach (KycOptions::YES_NO as $yn)
                        <option value="{{ $yn }}">{{ $yn }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="visas === 'نعم'" x-cloak class="md:col-span-2">
                <label class="label-dark">الدول التي صدرت منها التأشيرات</label>
                <textarea name="previous_visa_countries" rows="3" class="input-dark" :required="visas === 'نعم'" :disabled="visas !== 'نعم'">{{ old('previous_visa_countries', $record->previous_visa_countries) }}</textarea>
            </div>
        </div>
    </section>

    <section class="card space-y-4">
        <h3 class="border-b border-zinc-800 pb-2 text-sm font-semibold uppercase tracking-wider text-[#d4af37]">هـ — التوصية والحالة</h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="label-dark">التوصية</label>
                <textarea name="recommendation" rows="4" class="input-dark">{{ old('recommendation', $record->recommendation) }}</textarea>
            </div>
            <div>
                <label class="label-dark">حالة الملف</label>
                <select name="status" class="input-dark" required>
                    @foreach (KycOptions::STATUSES as $st)
                        <option value="{{ $st }}" @selected(old('status', $record->status) === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="btn-gold">حفظ</button>
        <a href="{{ url()->previous() }}" class="btn-outline-gold">إلغاء</a>
    </div>
</div>
