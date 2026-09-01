<x-maritime-layout>
    <main class="flex-grow max-w-[1280px] w-full mx-auto px-gutter py-xl">
        <!-- Hero Section -->
        <section class="mb-xl max-w-3xl mx-auto text-center">
            <h2 class="text-display-xl text-primary mb-md">About & Safety Guide</h2>
            <p class="text-body-lg text-on-surface-variant">Navigating the principles of Digital Sovereignty and establishing community protocols for a secure and open maritime network.</p>
        </section>

        <!-- Bento Grid Content Layout -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-md max-w-5xl mx-auto">
            <!-- Main Content Card (Span 8) -->
            <div class="md:col-span-8 bg-surface-container-lowest rounded-xl shadow-sm p-margin border border-surface-variant/30 flex flex-col gap-md">
                <div class="flex items-center gap-sm mb-xs">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">account_balance</span>
                    </div>
                    <h3 class="text-headline-lg text-primary">Digital Sovereignty</h3>
                </div>
                <div class="prose prose-lg text-body-md text-on-surface-variant space-y-md">
                    <p>
                        At the core of our platform lies the unwavering principle of Digital Sovereignty. We believe that your data, your identity, and your interactions within the digital maritime space belong entirely to you. Our architecture is designed to empower organizations and professionals by ensuring absolute control over their digital assets.
                    </p>
                    <p>
                        This means moving away from centralized silos. We utilize decentralized protocols where applicable, ensuring that the structural reliability of our network is not dependent on a single point of failure. Just as a vessel relies on its internal systems and navigation tools rather than a shore-based tether, your digital presence should be autonomous, secure, and resilient against external interference.
                    </p>
                </div>
            </div>

            <!-- Side Cards (Span 4) -->
            <div class="md:col-span-4 flex flex-col gap-md">
                <!-- Protocol Card -->
                <div class="bg-surface-container-lowest rounded-xl shadow-sm p-md border border-surface-variant/30 relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="absolute top-0 right-0 p-sm text-secondary opacity-20 group-hover:opacity-100 transition-opacity">
                        <span class="material-symbols-outlined" style="font-size: 48px;">policy</span>
                    </div>
                    <h4 class="text-headline-md text-primary mb-sm relative z-10">Community Protocols</h4>
                    <ul class="space-y-sm text-body-md text-on-surface-variant relative z-10">
                        <li class="flex items-start gap-xs">
                            <span class="material-symbols-outlined text-secondary text-[20px] mt-1">check_circle</span>
                            <span>Verify identities before sharing sensitive navigation or operational data.</span>
                        </li>
                        <li class="flex items-start gap-xs">
                            <span class="material-symbols-outlined text-secondary text-[20px] mt-1">check_circle</span>
                            <span>Report anomalies in signal strength or unexpected network behaviors immediately.</span>
                        </li>
                    </ul>
                </div>

                <!-- Emergency Contact Card -->
                <div class="bg-primary-container text-on-primary-container rounded-xl shadow-lg p-md flex flex-col justify-between items-start">
                    <div>
                        <div class="flex items-center gap-xs mb-sm text-primary-fixed-dim">
                            <span class="material-symbols-outlined">sos</span>
                            <span class="text-label-md uppercase tracking-wider">Critical Support</span>
                        </div>
                        <h4 class="text-headline-md text-on-primary mb-xs">Emergency Channel</h4>
                        <p class="text-body-md text-on-primary/80">For immediate assistance regarding data breaches or critical system failures.</p>
                    </div>
                    <button class="mt-lg px-4 py-2 border-2 border-secondary text-secondary rounded-lg font-semibold text-label-md hover:bg-secondary hover:text-white transition-colors flex items-center gap-xs">
                        <span class="material-symbols-outlined text-[18px]">call</span>
                        Initiate Contact
                    </button>
                </div>
            </div>

            <!-- Full Width Image Feature -->
            <div class="md:col-span-12 rounded-xl overflow-hidden h-[300px] mt-lg relative shadow-md">
                <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCp-Ham5aXtGbR88w6HP59GrZ93_adzmBJlPvk0c9FP0QW2cmV7xf2AIhlDs8uRcGuXnq1KSeLqqwCujAiygKIe_o7ZUEQ8veacg0hWkWmbrnd6u2TGWGtbFhnvNRGfCpZWhzrzfgpvLw5MhLWinF88kXmvU3RLbjGbV34o6IX6Dtp7KmXU0gooCWEC0de0W0266PeNtLSogxp2DJa2yV0G6TpY8xi5xPvXVIV50du0VndVJuayR61ov4l0El5O5hDtXWcCZSrzXA" alt="Coastal navigation" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent flex items-end p-margin">
                    <p class="text-headline-md text-on-primary max-w-2xl italic">"Security is not a destination, but an ongoing navigational process."</p>
                </div>
            </div>
        </div>
    </main>
</x-maritime-layout>
