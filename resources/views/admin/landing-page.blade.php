<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white">Landing Page</h2>
            <p class="text-sm text-slate-400">Edit the copy shown on the public homepage</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <p class="mb-4 rounded-xl bg-emerald-500/15 px-4 py-3 text-sm font-medium text-emerald-300">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('admin.landing-page.update') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Images</h3>
                    <p class="mt-1 text-sm text-slate-400">Leave a field blank to keep the current image. Each image is capped at 2MB.</p>
                    <div class="mt-4 grid gap-6 md:grid-cols-3">
                        <label class="flex flex-col gap-2 text-sm">
                            <span class="font-medium text-slate-300">Hero image</span>
                            @if ($content->hero_image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($content->hero_image_path) }}" alt="Current hero image" class="h-24 w-full rounded-lg object-cover">
                            @endif
                            <input type="file" name="hero_image" accept="image/*" class="text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-2 file:text-white">
                            <x-input-error :messages="$errors->get('hero_image')" />
                        </label>
                        <label class="flex flex-col gap-2 text-sm">
                            <span class="font-medium text-slate-300">"It's simple to start" image</span>
                            @if ($content->how_it_works_image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($content->how_it_works_image_path) }}" alt="Current how-it-works image" class="h-24 w-full rounded-lg object-cover">
                            @endif
                            <input type="file" name="how_it_works_image" accept="image/*" class="text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-2 file:text-white">
                            <x-input-error :messages="$errors->get('how_it_works_image')" />
                        </label>
                        <label class="flex flex-col gap-2 text-sm">
                            <span class="font-medium text-slate-300">"Payment links &amp; QR codes" image</span>
                            @if ($content->payment_links_image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($content->payment_links_image_path) }}" alt="Current payment links image" class="h-24 w-full rounded-lg object-cover">
                            @endif
                            <input type="file" name="payment_links_image" accept="image/*" class="text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-2 file:text-white">
                            <x-input-error :messages="$errors->get('payment_links_image')" />
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Payment method logos (4)</h3>
                    <p class="mt-1 text-sm text-slate-400">Leave the image blank to keep a styled text placeholder for that logo.</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @for ($i = 0; $i < 4; $i++)
                            @php $logo = old('payment_logos.'.$i, $content->payment_logos[$i] ?? ['label' => '', 'image_path' => null]); @endphp
                            <div class="rounded-xl bg-slate-800/60 p-4 space-y-3">
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Label {{ $i + 1 }}</span>
                                    <input type="text" name="payment_logos[{{ $i }}][label]" value="{{ $logo['label'] }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                </label>
                                <label class="flex flex-col gap-2 text-sm">
                                    <span class="font-medium text-slate-300">Logo image {{ $i + 1 }}</span>
                                    @if (! empty($logo['image_path']))
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($logo['image_path']) }}" alt="{{ $logo['label'] }}" class="h-10 w-auto max-w-[8rem] rounded bg-white object-contain p-1">
                                    @endif
                                    <input type="file" name="payment_logos[{{ $i }}][image]" accept="image/*" class="text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-950 file:px-3 file:py-2 file:text-white">
                                </label>
                            </div>
                        @endfor
                        <x-input-error :messages="$errors->get('payment_logos')" />
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Hero</h3>
                    <div class="mt-4 grid gap-4">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Badge text</span>
                            <input type="text" name="hero_badge_text" value="{{ old('hero_badge_text', $content->hero_badge_text) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('hero_badge_text')" />
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Headline</span>
                            <input type="text" name="hero_headline" value="{{ old('hero_headline', $content->hero_headline) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                            <x-input-error :messages="$errors->get('hero_headline')" />
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Subtext</span>
                            <textarea name="hero_subtext" rows="3" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>{{ old('hero_subtext', $content->hero_subtext) }}</textarea>
                            <x-input-error :messages="$errors->get('hero_subtext')" />
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Hero stats</h3>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Stat 1 label</span>
                            <input type="text" name="stat_1_label" value="{{ old('stat_1_label', $content->stat_1_label) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Stat 1 value</span>
                            <input type="text" name="stat_1_value" value="{{ old('stat_1_value', $content->stat_1_value) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Stat 2 label</span>
                            <input type="text" name="stat_2_label" value="{{ old('stat_2_label', $content->stat_2_label) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Stat 2 value</span>
                            <input type="text" name="stat_2_value" value="{{ old('stat_2_value', $content->stat_2_value) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">Features (4)</h3>
                    <div class="mt-4 space-y-4">
                        @for ($i = 0; $i < 4; $i++)
                            @php $feature = old('features.'.$i, $content->features[$i] ?? ['title' => '', 'description' => '']); @endphp
                            <div class="rounded-xl bg-slate-800/60 p-4 grid gap-3 md:grid-cols-[1fr_2fr]">
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Title {{ $i + 1 }}</span>
                                    <input type="text" name="features[{{ $i }}][title]" value="{{ $feature['title'] }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                </label>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Description {{ $i + 1 }}</span>
                                    <input type="text" name="features[{{ $i }}][description]" value="{{ $feature['description'] }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                </label>
                            </div>
                        @endfor
                        <x-input-error :messages="$errors->get('features')" />
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">FAQ (5)</h3>
                    <div class="mt-4 space-y-4">
                        @for ($i = 0; $i < 5; $i++)
                            @php $faq = old('faqs.'.$i, $content->faqs[$i] ?? ['question' => '', 'answer' => '']); @endphp
                            <div class="rounded-xl bg-slate-800/60 p-4 space-y-3">
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Question {{ $i + 1 }}</span>
                                    <input type="text" name="faqs[{{ $i }}][question]" value="{{ $faq['question'] }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                                </label>
                                <label class="flex flex-col gap-1 text-sm">
                                    <span class="font-medium text-slate-300">Answer {{ $i + 1 }}</span>
                                    <textarea name="faqs[{{ $i }}][answer]" rows="2" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>{{ $faq['answer'] }}</textarea>
                                </label>
                            </div>
                        @endfor
                        <x-input-error :messages="$errors->get('faqs')" />
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 p-6 shadow-sm ring-1 ring-white/10">
                    <h3 class="text-lg font-semibold text-white">CTA banner</h3>
                    <div class="mt-4 grid gap-4">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Heading</span>
                            <input type="text" name="cta_banner_heading" value="{{ old('cta_banner_heading', $content->cta_banner_heading) }}" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium text-slate-300">Subtext</span>
                            <textarea name="cta_banner_subtext" rows="2" class="rounded-xl border border-white/10 bg-slate-950 text-white px-3 py-2" required>{{ old('cta_banner_subtext', $content->cta_banner_subtext) }}</textarea>
                        </label>
                    </div>
                </div>

                <button type="submit" class="rounded-xl bg-lime-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-lime-300">Save changes</button>
            </form>
        </div>
    </div>
</x-app-layout>
