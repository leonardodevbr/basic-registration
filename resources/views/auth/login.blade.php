<x-guest-layout>
    {{-- Container tela cheia --}}
    <div class="flex flex-col w-full md:flex-row h-screen">

        {{-- Coluna esquerda - Formulário --}}
        <div class="w-full md:w-2/5 bg-[#f8ecda] flex items-center justify-center px-6 md:px-10 py-8 md:py-0">
            <div class="w-full max-w-md mx-auto">

                {{-- Logo + Nome --}}
                <div class="flex flex-row items-center sm:items-center gap-4 sm:gap-6 justify-center sm:justify-start">
                    <x-application-logo class="w-24 sm:w-[180px] rounded-full" />
                    <span class="boldonse-regular text-[#004b6b] text-[40px] sm:text-[65px] mt-6 sm:mt-10 leading-tight">SIViS</span>
                </div>

                {{-- Subtítulo --}}
                <h2 class="text-base sm:text-lg text-gray-700 font-semibold text-center sm:text-left mt-6 mb-6">
                    Sistema Integrado de Vigilância Socioassistencial
                </h2>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Login -->
                    <div>
                        <x-input-label for="login" :value="__('Matrícula ou E-mail:')" />
                        <x-text-input id="login" class="block mt-1 w-full" type="text" name="login" :value="old('login')" required autofocus autocomplete="login" />
                        <x-input-error :messages="$errors->get('login')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Senha:')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                            <span class="ml-2 text-sm text-gray-600">{{ __('Manter conectado') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-indigo-600 hover:underline" href="{{ route('password.request') }}">
                                {{ __('Esqueceu sua senha?') }}
                            </a>
                        @endif
                    </div>

                    <!-- Submit -->
                    <div>
                        <x-primary-button class="w-full justify-center bg-[#004b6b] py-3 hover:bg-[#00384e]">
                            {{ __('Acessar') }}
                        </x-primary-button>
                    </div>
                </form>

                <p class="mt-6 text-xs text-gray-400 text-center sm:text-left">
                    Acesso exclusivo para profissionais autorizados.
                </p>
            </div>
        </div>

        {{-- Coluna direita - Apresentação --}}
        <div class="w-full md:w-3/5 h-full bg-[#004b6b] text-white flex items-center justify-center md:p-10 relative">
            {{-- Fundo com ícones --}}
            <div class="absolute inset-0 opacity-5 z-0 bg-login" style="background-image: url('{{ asset('/img/bg-login.png') }}')"></div>

            <div class="z-10 px-6 md:py-12 py-3 md:px-10 md:py-0 text-center md:text-left max-w-xl flex flex-col justify-center h-full">
                <div class="font-bold mb-4 boldonse-regular leading-tight slogan flex flex-col">
                    <span>Acompanhamento social com</span>
                    <h1>inteligência e humanidade.</h1>
                </div>
                <p class="text-base md:text-lg text-blue-100 leading-relaxed">
                    O SIViS fortalece o trabalho das equipes da assistência social com dados precisos, organização e agilidade na gestão de pessoas, famílias e territórios.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
