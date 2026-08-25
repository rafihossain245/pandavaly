@extends('layout.app')

@section('meta-information')
    <title>Website Settings</title>
@endsection

@section('css')
<style>
    .ws-page { padding-bottom: 30px; }

    .ws-header { display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px 20px; margin-bottom: 18px; }
    .ws-header h1 { font-size: 1.35rem; font-weight: 600; margin: 0; color: #111827; }
    .ws-header p { margin: 4px 0 0; font-size: .85rem; color: #6b7280; }

    .ws-alert { display: flex; align-items: flex-start; gap: 10px; border-radius: 9px; padding: 12px 15px; margin-bottom: 18px; font-size: .87rem; border: 1px solid transparent; }
    .ws-alert i { margin-top: 2px; }
    .ws-alert-success { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
    .ws-alert-error { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
    .ws-alert ul { margin: 4px 0 0; padding-left: 18px; }

    .ws-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 16px; overflow: hidden; }
    .ws-card-head { display: flex; align-items: center; gap: 11px; padding: 14px 20px; border-bottom: 1px solid #f1f2f4; background: #fafafa; }
    .ws-card-icon { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .88rem; flex: 0 0 auto; }
    .ws-i-blue { background: #eff6ff; color: #2563eb; }
    .ws-i-green { background: #ecfdf5; color: #059669; }
    .ws-i-violet { background: #f5f3ff; color: #7c3aed; }
    .ws-card-head h2 { font-size: .98rem; font-weight: 600; margin: 0; color: #111827; }
    .ws-card-head p { margin: 2px 0 0; font-size: .78rem; color: #6b7280; }
    .ws-card-body { padding: 20px; }

    .ws-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .ws-full { grid-column: 1 / -1; }

    .ws-field > label { display: block; font-size: .84rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .ws-req { color: #ef4444; }
    .ws-input { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 7px; font-size: .88rem; background: #fff; color: #111827; }
    .ws-input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.12); }
    .ws-input.is-bad { border-color: #ef4444; }
    .ws-help { font-size: .77rem; color: #6b7280; margin: 5px 0 0; }
    .ws-err { font-size: .77rem; color: #dc2626; margin: 5px 0 0; }
    textarea.ws-input { resize: vertical; min-height: 84px; }

    /* ---- Image pickers ---- */
    .ws-image-row { display: flex; gap: 16px; align-items: flex-start; }
    .ws-preview { width: 116px; height: 78px; border: 1px dashed #d1d5db; border-radius: 8px; background: #fafafa; display: flex; align-items: center; justify-content: center; overflow: hidden; flex: 0 0 auto; padding: 8px; }
    .ws-preview.is-square { width: 78px; }
    .ws-preview img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .ws-preview span { font-size: .7rem; color: #9ca3af; text-align: center; }
    .ws-image-fields { flex: 1; min-width: 0; }

    /* ---- Social rows ---- */
    .ws-social-row { display: flex; align-items: center; gap: 12px; padding: 11px 0; border-bottom: 1px solid #f6f7f8; }
    .ws-social-row:last-child { border-bottom: none; }
    .ws-social-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .92rem; flex: 0 0 auto; }
    .ws-social-label { width: 104px; font-size: .85rem; font-weight: 600; color: #374151; flex: 0 0 auto; }
    .ws-social-row .ws-input { flex: 1; }
    .ws-social-test { color: #9ca3af; padding: 6px 8px; border-radius: 6px; }
    .ws-social-test:hover { color: #2563eb; background: #eff6ff; }
    .ws-social-test.is-off { visibility: hidden; }

    /* ---- Sticky save ---- */
    .ws-save-bar { position: sticky; bottom: 0; z-index: 30; display: flex; align-items: center; justify-content: flex-end; gap: 12px; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 13px 18px; box-shadow: 0 -3px 14px rgba(17,24,39,.07); }
    .ws-save-note { margin-right: auto; font-size: .78rem; color: #6b7280; }
    .ws-btn { display: inline-flex; align-items: center; gap: 7px; border-radius: 7px; padding: 9px 18px; font-size: .88rem; font-weight: 500; cursor: pointer; border: 1px solid transparent; text-decoration: none; }
    .ws-btn-primary { background: #2563eb; color: #fff; }
    .ws-btn-primary:hover { background: #1d4ed8; color: #fff; }
    .ws-btn-light { background: #fff; border-color: #d1d5db; color: #374151; }
    .ws-btn-light:hover { background: #f9fafb; color: #374151; }

    @media (max-width: 720px) {
        .ws-grid { grid-template-columns: 1fr; }
        .ws-social-row { flex-wrap: wrap; }
        .ws-social-label { width: auto; }
    }
</style>
@endsection

@section('main-content')
@php
    $roleSlug = Str::slug(Auth::user()->getRoleNames()->first());
    // Brand colours for the social icon chips, keyed to Setting::SOCIAL_PLATFORMS.
    $socialColors = [
        'facebook_url' => '#1877f2',
        'instagram_url' => '#e1306c',
        'twitter_url' => '#111827',
        'youtube_url' => '#ff0000',
        'linkedin_url' => '#0a66c2',
        'tiktok_url' => '#111827',
    ];
@endphp

<div class="ws-page">
    <div class="ws-header">
        <div>
            <h1>Website Settings</h1>
            <p>Your shop's identity, contact details and social profiles — used across the storefront.</p>
        </div>
        <a href="{{ route('home') }}" target="_blank" class="ws-btn ws-btn-light">
            <i class="fas fa-arrow-up-right-from-square"></i> View storefront
        </a>
    </div>

    @if (session('success'))
        <div class="ws-alert ws-alert-success">
            <i class="fas fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="ws-alert ws-alert-error">
            <i class="fas fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="ws-alert ws-alert-error">
            <i class="fas fa-circle-exclamation"></i>
            <div>
                <strong>{{ $errors->count() }} {{ Str::plural('field', $errors->count()) }} need{{ $errors->count() === 1 ? 's' : '' }} your attention.</strong>
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('role.website-settings.store', ['role' => $roleSlug]) }}"
          method="POST" enctype="multipart/form-data">
        @csrf

        {{-- ---------------------------------------------------- Brand identity --}}
        <div class="ws-card">
            <div class="ws-card-head">
                <div class="ws-card-icon ws-i-blue"><i class="fas fa-store"></i></div>
                <div>
                    <h2>Brand identity</h2>
                    <p>The name and marks shoppers see in the browser tab and header.</p>
                </div>
            </div>
            <div class="ws-card-body">
                <div class="ws-grid">
                    <div class="ws-field ws-full">
                        <label for="title">Website title <span class="ws-req">*</span></label>
                        <input type="text" name="title" id="title"
                               value="{{ old('title', $settings->title ?? '') }}"
                               class="ws-input @error('title') is-bad @enderror"
                               placeholder="e.g. GoeBazar">
                        @error('title')
                            <p class="ws-err">{{ $message }}</p>
                        @else
                            <p class="ws-help">Shown in the browser tab and used as the footer copyright name.</p>
                        @enderror
                    </div>

                    <div class="ws-field">
                        <label for="logo_path">Logo</label>
                        <div class="ws-image-row">
                            <div class="ws-preview" id="logo_preview">
                                @if (! empty($settings->logo_path))
                                    <img src="{{ asset($settings->logo_path) }}" alt="Current logo">
                                @else
                                    <span>No logo</span>
                                @endif
                            </div>
                            <div class="ws-image-fields">
                                <input type="file" name="logo_path" id="logo_path" accept="image/*"
                                       class="ws-input @error('logo_path') is-bad @enderror">
                                @error('logo_path')
                                    <p class="ws-err">{{ $message }}</p>
                                @else
                                    <p class="ws-help">PNG with a transparent background works best. Up to 2 MB.</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="ws-field">
                        <label for="og_image_path">Social share image</label>
                        <div class="ws-image-row">
                            <div class="ws-preview" id="og_image_preview">
                                @if (! empty($settings->og_image_path))
                                    <img src="{{ asset($settings->og_image_path) }}" alt="Current share image">
                                @else
                                    <span>Falls back to logo</span>
                                @endif
                            </div>
                            <div class="ws-image-fields">
                                <input type="file" name="og_image_path" id="og_image_path" accept="image/jpeg,image/png,image/webp"
                                       class="ws-input @error('og_image_path') is-bad @enderror">
                                @error('og_image_path')
                                    <p class="ws-err">{{ $message }}</p>
                                @else
                                    <p class="ws-help">
                                        Shown when your link is shared on Facebook, WhatsApp or Messenger.
                                        <strong>1200&times;630</strong> works best. JPG, PNG or WEBP — social
                                        platforms do not render SVG.
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="ws-field">
                        <label for="favicon_path">Favicon</label>
                        <div class="ws-image-row">
                            <div class="ws-preview is-square" id="favicon_preview">
                                @if (! empty($settings->favicon_path))
                                    <img src="{{ asset($settings->favicon_path) }}" alt="Current favicon">
                                @else
                                    <span>None</span>
                                @endif
                            </div>
                            <div class="ws-image-fields">
                                <input type="file" name="favicon_path" id="favicon_path" accept="image/*"
                                       class="ws-input @error('favicon_path') is-bad @enderror">
                                @error('favicon_path')
                                    <p class="ws-err">{{ $message }}</p>
                                @else
                                    <p class="ws-help">Small square image, 32&times;32 or 64&times;64.</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ---------------------------------------------------- Contact --}}
        <div class="ws-card">
            <div class="ws-card-head">
                <div class="ws-card-icon ws-i-green"><i class="fas fa-headset"></i></div>
                <div>
                    <h2>Contact details</h2>
                    <p>Shown in the footer. The phone number also powers the floating WhatsApp button.</p>
                </div>
            </div>
            <div class="ws-card-body">
                <div class="ws-grid">
                    <div class="ws-field">
                        <label for="contact_phone">Contact phone</label>
                        <input type="tel" name="contact_phone" id="contact_phone"
                               value="{{ old('contact_phone', $settings->contact_phone ?? '') }}"
                               class="ws-input @error('contact_phone') is-bad @enderror"
                               placeholder="01770105856">
                        @error('contact_phone')
                            <p class="ws-err">{{ $message }}</p>
                        @else
                            <p class="ws-help">Leave empty to hide the WhatsApp button.</p>
                        @enderror
                    </div>

                    <div class="ws-field">
                        <label for="contact_email">Contact email</label>
                        <input type="email" name="contact_email" id="contact_email"
                               value="{{ old('contact_email', $settings->contact_email ?? '') }}"
                               class="ws-input @error('contact_email') is-bad @enderror"
                               placeholder="support@yourshop.com">
                        @error('contact_email')
                            <p class="ws-err">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="ws-field ws-full">
                        <label for="address">Address</label>
                        <textarea name="address" id="address" rows="3"
                                  class="ws-input @error('address') is-bad @enderror"
                                  placeholder="Shop address shown in the footer">{{ old('address', $settings->address ?? '') }}</textarea>
                        @error('address')
                            <p class="ws-err">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ---------------------------------------------------- Social --}}
        <div class="ws-card">
            <div class="ws-card-head">
                <div class="ws-card-icon ws-i-violet"><i class="fas fa-share-nodes"></i></div>
                <div>
                    <h2>Social media</h2>
                    <p>Appear in the top bar and the footer. Leave one empty and its icon is hidden.</p>
                </div>
            </div>
            <div class="ws-card-body">
                @foreach (\App\Models\Setting::SOCIAL_PLATFORMS as $key => $platform)
                    @php $value = old($key, $settings->{$key} ?? ''); @endphp
                    <div class="ws-social-row">
                        <span class="ws-social-icon" style="background: {{ $socialColors[$key] ?? '#6b7280' }}">
                            <i class="{{ $platform['icon'] }}"></i>
                        </span>
                        <span class="ws-social-label">{{ $platform['label'] }}</span>
                        <input type="text" name="{{ $key }}" id="{{ $key }}" value="{{ $value }}"
                               class="ws-input ws-social-input @error($key) is-bad @enderror"
                               placeholder="{{ $platform['placeholder'] }}">
                        <a href="{{ $value ?: '#' }}" target="_blank" rel="noopener"
                           class="ws-social-test {{ $value ? '' : 'is-off' }}" title="Open this profile">
                            <i class="fas fa-arrow-up-right-from-square"></i>
                        </a>
                    </div>
                    @error($key)
                        <p class="ws-err">{{ $message }}</p>
                    @enderror
                @endforeach

                <p class="ws-help" style="margin-top:12px;">
                    Paste the full profile address. If you leave off <code>https://</code> we add it for you.
                </p>
            </div>
        </div>

        {{-- ---------------------------------------------- Storefront copy --}}
        <div class="ws-card">
            <div class="ws-card-head">
                <div class="ws-card-icon ws-i-violet"><i class="fas fa-pen-nib"></i></div>
                <div>
                    <h2>Storefront copy</h2>
                    <p>Wording shown to shoppers. Leave the tagline empty to hide it.</p>
                </div>
            </div>
            <div class="ws-card-body">
                <div class="ws-grid">
                    <div class="ws-field ws-full">
                        <label for="tagline">Footer tagline</label>
                        <input type="text" name="tagline" id="tagline"
                               value="{{ old('tagline', $settings->tagline ?? '') }}"
                               class="ws-input @error('tagline') is-bad @enderror"
                               placeholder="e.g. Premium bedsheets and home textiles, delivered across Bangladesh">
                        @error('tagline')<p class="ws-err">{{ $message }}</p>@enderror
                        <p class="ws-help">Appears under the logo in the footer. Leave empty to hide it.</p>
                    </div>
                    <div class="ws-field ws-full">
                        <label for="meta_description">Search &amp; share description</label>
                        <input type="text" name="meta_description" id="meta_description"
                               value="{{ old('meta_description', $settings->meta_description ?? '') }}"
                               class="ws-input @error('meta_description') is-bad @enderror"
                               placeholder="e.g. প্রিমিয়াম বেডশিট — সারা বাংলাদেশে ক্যাশ অন ডেলিভারি">
                        @error('meta_description')<p class="ws-err">{{ $message }}</p>@enderror
                        <p class="ws-help">One sentence used by Google and by the preview card when the site is shared on
                           Facebook, WhatsApp or Messenger. Falls back to the footer tagline.</p>
                    </div>
                    <div class="ws-field ws-full">
                        <label for="announcement">Announcement bar</label>
                        <input type="text" name="announcement" id="announcement"
                               value="{{ old('announcement', $settings->announcement ?? '') }}"
                               class="ws-input @error('announcement') is-bad @enderror"
                               placeholder="e.g. ধামাকা অফার — ১ পিস বেডশিট এর সাথে উপহার ফ্রি">
                        @error('announcement')<p class="ws-err">{{ $message }}</p>@enderror
                    </div>
                </div>

                <label class="ws-social-row" style="border:0; padding-top:10px;">
                    <input type="checkbox" name="announcement_enabled" value="1"
                           {{ old('announcement_enabled', $settings->announcement_enabled ?? false) ? 'checked' : '' }}
                           style="width:16px;height:16px;margin-right:10px;">
                    <span style="font-size:.9rem;color:#374151;">Show the announcement bar above the header</span>
                </label>
            </div>
        </div>

        {{-- ------------------------------------------------- Landing page --}}
        <div class="ws-card">
            <div class="ws-card-head">
                <div class="ws-card-icon ws-i-violet"><i class="fas fa-bullseye"></i></div>
                <div>
                    <h2>Landing page</h2>
                    <p>Headings on the one-page order funnel and its receipt. Leave a field empty to keep the
                       wording shown as the placeholder.</p>
                </div>
            </div>
            <div class="ws-card-body">
                <div class="ws-grid">
                    @foreach(\App\Models\Setting::LANDING_COPY as $key => $field)
                        <div class="ws-field ws-full">
                            <label for="{{ $key }}">{{ $field['label'] }}</label>
                            <input type="text" name="{{ $key }}" id="{{ $key }}"
                                   value="{{ old($key, $settings->{$key} ?? '') }}"
                                   class="ws-input @error($key) is-bad @enderror"
                                   placeholder="{{ $field['default'] }}">
                            @error($key)<p class="ws-err">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
                <p class="ws-help" style="margin-top:12px;">
                    Products, prices and the offer banner are not set here — they come from Products,
                    Website Management → Banners and the customer reviews section under Homepage Sections.
                </p>
            </div>
        </div>

        {{-- -------------------------------------------------------- Sounds --}}
        <div class="ws-card">
            <div class="ws-card-head">
                <div class="ws-card-icon ws-i-violet"><i class="fas fa-volume-high"></i></div>
                <div>
                    <h2>Sounds</h2>
                    <p>Optional audio cues. Leave empty for a silent site.</p>
                </div>
            </div>
            <div class="ws-card-body">
                <div class="ws-alert ws-alert-error" style="background:#fffbeb; border-color:#fde68a; color:#92400e;">
                    <i class="fas fa-circle-info"></i>
                    <div>
                        <strong>Browsers block sound until the visitor interacts with the page.</strong>
                        The welcome cue therefore plays on the visitor's first tap, scroll or click —
                        not the instant the page opens. Visitors can mute it, and the choice is remembered.
                    </div>
                </div>

                @php
                    $sounds = [
                        'welcome_audio_path' => ['label' => 'Welcome sound', 'help' => 'Plays once on the shop\'s first page a visitor opens.'],
                        'order_audio_path' => ['label' => 'Order sound', 'help' => 'Plays on the order-received page after checkout.'],
                    ];
                @endphp

                @foreach ($sounds as $key => $sound)
                    <div class="ws-field" style="margin-bottom:18px;">
                        <label for="{{ $key }}">{{ $sound['label'] }}</label>
                        @if (! empty($settings->{$key}))
                            {{-- A player, so the admin can check what is stored without
                                 opening the storefront and waiting for the cue. --}}
                            <audio controls preload="none" src="{{ asset($settings->{$key}) }}"
                                   style="width:100%; max-width:420px; margin-bottom:8px;"></audio>
                        @endif
                        <input type="file" name="{{ $key }}" id="{{ $key }}" accept="audio/mpeg,audio/mp3,audio/wav,audio/ogg"
                               class="ws-input @error($key) is-bad @enderror">
                        @error($key)
                            <p class="ws-err">{{ $message }}</p>
                        @else
                            <p class="ws-help">{{ $sound['help'] }} MP3, WAV or OGG, up to 3 MB. Keep it under ~3 seconds.</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>

        {{-- --------------------------------------------------- Mobile app --}}
        <div class="ws-card">
            <div class="ws-card-head">
                <div class="ws-card-icon ws-i-violet"><i class="fas fa-mobile-screen"></i></div>
                <div>
                    <h2>Mobile app</h2>
                    <p>Shown as download badges in the footer. Leave both empty to hide the block.</p>
                </div>
            </div>
            <div class="ws-card-body">
                @php
                    $appStores = [
                        'play_store_url' => ['label' => 'Google Play', 'icon' => 'fab fa-google-play', 'color' => '#01875f', 'placeholder' => 'play.google.com/store/apps/details?id=…'],
                        'app_store_url' => ['label' => 'App Store', 'icon' => 'fab fa-apple', 'color' => '#000000', 'placeholder' => 'apps.apple.com/app/…'],
                    ];
                @endphp
                @foreach ($appStores as $key => $store)
                    @php $value = old($key, $settings->{$key} ?? ''); @endphp
                    <div class="ws-social-row">
                        <span class="ws-social-icon" style="background: {{ $store['color'] }}">
                            <i class="{{ $store['icon'] }}"></i>
                        </span>
                        <span class="ws-social-label">{{ $store['label'] }}</span>
                        <input type="text" name="{{ $key }}" id="{{ $key }}" value="{{ $value }}"
                               class="ws-input @error($key) is-bad @enderror"
                               placeholder="{{ $store['placeholder'] }}">
                        <a href="{{ $value ?: '#' }}" target="_blank" rel="noopener"
                           class="ws-social-test {{ $value ? '' : 'is-off' }}" title="Open this listing">
                            <i class="fas fa-arrow-up-right-from-square"></i>
                        </a>
                    </div>
                    @error($key)
                        <p class="ws-err">{{ $message }}</p>
                    @enderror
                @endforeach
            </div>
        </div>

        {{-- ---------------------------------------------------- Tracking --}}
        <div class="ws-card">
            <div class="ws-card-head">
                <div class="ws-card-icon ws-i-blue"><i class="fas fa-bullseye"></i></div>
                <div>
                    <h2>Marketing &amp; tracking</h2>
                    <p>Measure ads and traffic. Paste the ID only — we build the tracking code for you.</p>
                </div>
            </div>
            <div class="ws-card-body">
                @foreach (\App\Models\Setting::TRACKING_FIELDS as $key => $field)
                    <div class="ws-social-row">
                        <span class="ws-social-icon" style="background: {{ $field['colour'] }}">
                            <i class="{{ $field['icon'] }}"></i>
                        </span>
                        <span class="ws-social-label">{{ $field['label'] }}</span>
                        <input type="text" name="{{ $key }}" id="{{ $key }}"
                               value="{{ old($key, $settings->{$key} ?? '') }}"
                               class="ws-input ws-social-input @error($key) is-bad @enderror"
                               placeholder="{{ $field['placeholder'] }}">
                    </div>
                    @error($key)
                        <p class="ws-err">{{ $message }}</p>
                    @else
                        <p class="ws-help" style="margin: -4px 0 12px 44px;">{{ $field['help'] }}</p>
                    @enderror
                @endforeach

                <p class="ws-help" style="margin-top:12px;">
                    Leave a field empty and that platform's script is not loaded at all. Tracking runs on the
                    storefront only — staff browsing this dashboard are never counted as shoppers.
                </p>
            </div>
        </div>

        <div class="ws-save-bar">
            <span class="ws-save-note"><span class="ws-req">*</span> Required field</span>
            <button type="submit" class="ws-btn ws-btn-primary">
                <i class="fas fa-floppy-disk"></i> Save settings
            </button>
        </div>
    </form>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
$(function () {
    // Swap the preview to the newly picked file so the admin can confirm the
    // right image before saving.
    function bindPreview(inputId, previewId) {
        $('#' + inputId).on('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                $('#' + previewId).html($('<img>').attr('src', e.target.result).attr('alt', 'Preview'));
            };
            reader.readAsDataURL(file);
        });
    }

    bindPreview('logo_path', 'logo_preview');
    bindPreview('og_image_path', 'og_image_preview');
    bindPreview('favicon_path', 'favicon_preview');

    // Keep each row's "open profile" shortcut in step with what is typed.
    $('.ws-social-input').on('input', function () {
        const value = $(this).val().trim();
        const $test = $(this).next('.ws-social-test');
        const href = /^(https?:)?\/\//i.test(value) ? value : 'https://' + value.replace(/^\/+/, '');

        $test.toggleClass('is-off', value === '').attr('href', value ? href : '#');
    });
});
</script>
@endsection
