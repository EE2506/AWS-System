<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const initials = computed(() => user.value?.name?.charAt(0)?.toUpperCase() ?? '?');
const isAdmin = computed(() => user.value?.roles?.some((role) => role.name === 'admin') ?? false);
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_14px_rgba(52,211,153,0.7)]"></span>
                <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-300">
                    Account settings
                </h2>
            </div>
        </template>

        <div class="profile-page">
            <div class="mx-auto max-w-6xl">
                <div class="mb-8 max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-400/80">Personal workspace</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-white sm:text-4xl">Make the account feel like yours.</h1>
                    <p class="mt-3 text-sm leading-6 text-slate-400">Manage your identity, security, and account access from one calm place.</p>
                </div>

                <div class="grid gap-6 lg:grid-cols-[17rem_minmax(0,1fr)] lg:items-start">
                    <aside class="profile-identity">
                        <div class="profile-avatar">{{ initials }}</div>
                        <div class="mt-5 min-w-0">
                            <p class="truncate text-lg font-semibold text-white">{{ user?.name }}</p>
                            <p class="mt-1 truncate text-sm text-slate-400">{{ user?.email }}</p>
                        </div>
                        <div class="mt-6 flex items-center gap-2 text-xs font-medium uppercase tracking-[0.16em] text-slate-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            {{ isAdmin ? 'Administrator' : 'Member' }}
                        </div>
                        <div class="mt-8 border-t border-white/10 pt-5 text-xs leading-5 text-slate-500">
                            Keep your contact details current so document activity and account notices reach you reliably.
                        </div>
                    </aside>

                    <div class="space-y-5">
                        <section class="profile-panel profile-panel-primary">
                            <div class="profile-panel-index">01</div>
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                                class="profile-form"
                            />
                        </section>

                        <section class="profile-panel">
                            <div class="profile-panel-index">02</div>
                            <UpdatePasswordForm class="profile-form" />
                        </section>

                        <section class="profile-panel profile-panel-danger">
                            <div class="profile-panel-index">03</div>
                            <DeleteUserForm class="profile-form" />
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.profile-page {
    min-height: calc(100vh - 9rem);
    background:
        radial-gradient(circle at 84% 4%, rgba(16, 185, 129, 0.08), transparent 25rem),
        linear-gradient(135deg, rgba(15, 23, 42, 0.2), rgba(2, 6, 23, 0.9));
    margin: -2rem;
    padding: 2rem;
}

.profile-identity,
.profile-panel {
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(15, 23, 42, 0.72);
    box-shadow: 0 18px 50px rgba(2, 6, 23, 0.18);
    backdrop-filter: blur(18px);
}

.profile-identity {
    border-top: 2px solid rgba(52, 211, 153, 0.7);
    padding: 1.5rem;
    position: sticky;
    top: 5.5rem;
}

.profile-avatar {
    align-items: center;
    background: linear-gradient(145deg, #34d399, #0f766e);
    border: 1px solid rgba(167, 243, 208, 0.45);
    border-radius: 1rem;
    box-shadow: 0 12px 30px rgba(16, 185, 129, 0.22);
    color: #ecfdf5;
    display: flex;
    font-size: 2.5rem;
    font-weight: 700;
    height: 5.5rem;
    justify-content: center;
    letter-spacing: -0.06em;
    width: 5.5rem;
}

.profile-panel {
    display: grid;
    gap: 1rem;
    grid-template-columns: 2rem minmax(0, 1fr);
    padding: 1.5rem;
}

.profile-panel-primary {
    border-top: 2px solid rgba(52, 211, 153, 0.7);
}

.profile-panel-danger {
    border-color: rgba(248, 113, 113, 0.18);
}

.profile-panel-index {
    color: rgba(52, 211, 153, 0.85);
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    padding-top: 0.2rem;
}

:deep(.profile-form section header h2) {
    color: #f8fafc;
    font-size: 1rem;
    font-weight: 600;
    letter-spacing: -0.01em;
}

:deep(.profile-form section header p) {
    color: #94a3b8;
    line-height: 1.5;
}

:deep(.profile-form label) {
    color: #cbd5e1;
    font-size: 0.8rem;
    font-weight: 500;
}

:deep(.profile-form input) {
    background: rgba(2, 6, 23, 0.45);
    border-color: rgba(148, 163, 184, 0.2);
    color: #f8fafc;
}

:deep(.profile-form input:focus) {
    border-color: rgba(52, 211, 153, 0.8);
    box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.12);
}

@media (max-width: 640px) {
    .profile-page {
        margin: -1rem;
        padding: 1rem;
    }

    .profile-identity {
        position: static;
    }

    .profile-panel {
        grid-template-columns: 1fr;
        padding: 1.25rem;
    }

    .profile-panel-index {
        padding-top: 0;
    }
}

@media (prefers-reduced-motion: no-preference) {
    .profile-identity,
    .profile-panel {
        animation: profile-rise 0.55s ease-out both;
    }

    .profile-panel:nth-child(2) {
        animation-delay: 80ms;
    }

    .profile-panel:nth-child(3) {
        animation-delay: 160ms;
    }
}

@keyframes profile-rise {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
