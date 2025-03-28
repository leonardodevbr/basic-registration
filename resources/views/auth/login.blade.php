<x-guest-layout>
    {{-- Container responsivo --}}
    <div class="flex flex-col md:flex-row min-h-screen">

        {{-- Coluna esquerda - Formulário --}}
        <div class="w-full md:w-2/5 bg-[#f8ecda] flex flex-col justify-center px-6 md:px-10 py-10 md:py-0">
            <div class="max-w-md w-full mx-auto">

                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-6">
                    <x-application-logo class="w-28 sm:w-[180px] rounded-full" />
                    <span class="boldonse-regular text-[#004b6b] text-[40px] sm:text-[65px] leading-tight">SIViS</span>
                </div>

                <h2 class="text-base sm:text-lg text-gray-700 font-semibold text-center mt-6 mb-6">
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
                        <x-primary-button class="w-full justify-center bg-[#004b6b] hover:bg-[#00384e]">
                            {{ __('Acessar') }}
                        </x-primary-button>
                    </div>
                </form>

                <p class="mt-6 text-xs text-gray-400 text-center sm:text-left">Acesso exclusivo para profissionais autorizados.</p>
            </div>
        </div>

        {{-- Coluna direita - Apresentação --}}
        <div class="hidden md:flex w-3/5 bg-[#004b6b] text-white items-center justify-center p-10 relative overflow-hidden">
            {{-- Fundo com ícones, se quiser --}}
            <div class="absolute inset-0 bg-contain bg-center opacity-5 z-0" style="background-image: url('{{asset('/img/bg-login.png')}}')"></div>

            <div class="max-w-xl z-10">
                <h1 class="text-3xl mb-4 boldonse-regular">Acompanhamento social com inteligência e humanidade.</h1>
                <p class="text-lg text-blue-100">
                    O SIVIS fortalece o trabalho das equipes da assistência social com dados precisos, organização e agilidade na gestão de pessoas, famílias e territórios.
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
