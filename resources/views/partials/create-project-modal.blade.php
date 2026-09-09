<div
    x-show="createProjectOpen"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
    @click.self="createProjectOpen = false"
    role="dialog"
    aria-modal="true"
    aria-label="New project"
>
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden">
        <form method="POST" action="{{ route('lists.store') }}">
            @csrf
            <div class="flex items-center justify-between px-6 pt-6 pb-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center">
                        <svg width="18" height="18" fill="none" stroke="#4f46e5" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="7" rx="1.5" />
                            <rect x="14" y="3" width="7" height="7" rx="1.5" />
                            <rect x="3" y="14" width="7" height="7" rx="1.5" />
                            <line x1="14" y1="17.5" x2="21" y2="17.5" />
                            <line x1="17.5" y1="14" x2="17.5" y2="21" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">New Project</h2>
                        <p class="text-xs text-slate-400">Set up a workspace for your team</p>
                    </div>
                </div>
                <button type="button" @click="createProjectOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors p-1" aria-label="Close">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="project-name">Project Name <span class="text-rose-500">*</span></label>
                    <input
                        id="project-name"
                        name="name"
                        required
                        maxlength="255"
                        placeholder="e.g. Website Redesign"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-shadow"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5" for="project-desc">Description <span class="text-slate-400 font-normal">(optional)</span></label>
                    <textarea
                        id="project-desc"
                        name="description"
                        rows="3"
                        placeholder="What is this project about?"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-shadow resize-none"
                    ></textarea>
                </div>

                <p class="text-[11px] text-slate-400">Members are managed from the project page (invite by email, Programmer 2 — F-11).</p>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/60">
                <button type="button" @click="createProjectOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors shadow-sm shadow-indigo-200">
                    Create Project
                </button>
            </div>
        </form>
    </div>
</div>
