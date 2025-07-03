@props(['dropdownClass' => 'dropdown'])

<div class="{{ $dropdownClass }}">
    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-globe {{ marginEnd('2') }}"></i>
        @if(app()->getLocale() === 'ar')
            العربية
        @elseif(app()->getLocale() === 'ku')
            کوردی
        @else
            English
        @endif
    </button>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}"
               href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}">
                🇺🇸 English
            </a>
        </li>
        <li>
            <a class="dropdown-item {{ app()->getLocale() === 'ar' ? 'active' : '' }}"
               href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}">
                🇮🇶 العربية
            </a>
        </li>
        <li>
            <a class="dropdown-item {{ app()->getLocale() === 'ku' ? 'active' : '' }}"
               href="{{ request()->fullUrlWithQuery(['lang' => 'ku']) }}">
                🇮🇶 کوردی
            </a>
        </li>
    </ul>
</div>
