<script setup lang="ts">
import { ArrowUpRight, Download } from '@lucide/vue';
import { computed } from 'vue';
import MotionReveal from '@/components/MotionReveal.vue';
import ProfileVisual from '@/components/public/ProfileVisual.vue';
import ProjectCard from '@/components/public/ProjectCard.vue';
import SeoHead from '@/components/public/SeoHead.vue';
import SocialLinks from '@/components/public/SocialLinks.vue';
import type { HomePageProps, Skill } from '@/types';

const props = defineProps<HomePageProps>();
const name = computed(
    () =>
        props.profile?.full_name ||
        props.settings.website_title ||
        'Personal Profile',
);
const aboutParagraphs = computed(() =>
    (props.profile?.about || props.profile?.short_bio || '')
        .split(/\n\s*\n/)
        .map((item) => item.trim())
        .filter(Boolean),
);
const groupedSkills = computed(() =>
    props.skills.reduce<Record<string, Skill[]>>((groups, skill) => {
        const category = skill.category || 'Expertise';
        groups[category] ??= [];
        groups[category].push(skill);

        return groups;
    }, {}),
);
const contactHref = computed(() => {
    if (props.profile?.email) {
        return `mailto:${props.profile.email}`;
    }

    if (props.profile?.whatsapp) {
        return `https://wa.me/${props.profile.whatsapp.replace(/\D/g, '')}`;
    }

    if (props.settings.contact_email) {
        return `mailto:${props.settings.contact_email}`;
    }

    if (props.settings.contact_whatsapp) {
        return `https://wa.me/${props.settings.contact_whatsapp.replace(/\D/g, '')}`;
    }

    return props.socialLinks[0]?.url ?? '#';
});
const formatMonth = (value: string): string =>
    new Intl.DateTimeFormat('en', { month: 'short', year: 'numeric' }).format(
        new Date(value),
    );
const experiencePeriod = (
    start: string,
    end: string | null,
    current: boolean,
): string =>
    `${formatMonth(start)} — ${current || !end ? 'Present' : formatMonth(end)}`;
const trainingPeriod = (
    start: string | null,
    end: string | null,
): string | null => {
    if (!start && !end) {
        return null;
    }

    const format = (value: string): string =>
        new Intl.DateTimeFormat('en', {
            month: 'short',
            year: 'numeric',
        }).format(new Date(`${value}T00:00:00`));

    if (start && end) {
        return `${format(start)} — ${format(end)}`;
    }

    return format((start || end) as string);
};
</script>

<template>
    <SeoHead :seo="seo" />

    <section id="home" class="scroll-mt-2 overflow-hidden bg-white">
        <div
            class="content-container grid items-center gap-9 py-10 sm:py-14 lg:min-h-[36rem] lg:grid-cols-2 lg:gap-12 lg:py-16"
        >
            <div class="max-w-[36rem] min-w-0">
                <h1
                    class="text-[clamp(2.55rem,4.35vw,4.6rem)] leading-[0.9] font-semibold tracking-[-0.055em] text-[#171717]"
                >
                    <span class="hero-reveal-line"
                        ><span style="animation-delay: 80ms"
                            >FULL STACK</span
                        ></span
                    >
                    <span class="hero-reveal-line pr-1"
                        ><span
                            class="whitespace-nowrap text-[#315CFF]"
                            style="animation-delay: 150ms"
                            >WEB DEVELOPER</span
                        ></span
                    >
                </h1>
                <p
                    class="hero-reveal-line mt-6 max-w-[38rem] text-[0.95rem] leading-7 font-normal text-[var(--public-description)] sm:text-base"
                >
                    <span style="animation-delay: 210ms"
                        >I am a Full Stack Web Developer with approximately 4
                        years of experience, I build practical web applications,
                        business systems, and reliable digital solutions focused
                        on real-world needs.</span
                    >
                </p>
                <div v-if="profile?.cv_url" class="hero-reveal-line">
                    <a
                        :href="profile.cv_url"
                        download
                        class="mt-8 inline-flex min-h-12 items-center gap-2 rounded-lg bg-primary px-5 text-xs font-semibold tracking-[0.1em] text-white uppercase transition-colors hover:bg-[#244ce0]"
                        style="animation-delay: 280ms"
                    >
                        Download CV <Download class="size-4" />
                    </a>
                </div>
            </div>
            <div
                class="hero-reveal-line mx-auto aspect-[4/5] w-full max-w-[17rem] overflow-hidden bg-white sm:max-w-sm lg:max-w-[25rem]"
                style="animation-delay: 180ms"
            >
                <img
                    v-if="profile?.hero_image_url || profile?.profile_image_url"
                    :src="
                        profile.hero_image_url ||
                        profile.profile_image_url ||
                        undefined
                    "
                    :alt="
                        [name, profile.professional_title]
                            .filter(Boolean)
                            .join(' — ')
                    "
                    fetchpriority="high"
                    decoding="async"
                    class="h-full w-full object-contain"
                />
                <ProfileVisual
                    v-else
                    :name="name"
                    :professional-title="profile?.professional_title"
                />
            </div>
        </div>
    </section>

    <section
        id="about"
        class="scroll-mt-2 border-t border-border bg-white py-12 sm:py-14 lg:py-16"
        aria-labelledby="about-heading"
    >
        <div class="content-container">
            <div class="grid gap-8 lg:grid-cols-[0.34fr_1fr] lg:gap-14">
                <MotionReveal
                    ><div>
                        <p class="editorial-kicker">About</p>
                        <h2 id="about-heading" class="editorial-heading mt-3">
                            A little context.
                        </h2>
                    </div></MotionReveal
                >
                <MotionReveal :delay="70">
                    <div class="max-w-[45rem] space-y-5">
                        <p
                            v-for="(paragraph, index) in aboutParagraphs"
                            :key="index"
                            class="leading-7 text-muted-foreground"
                            :class="
                                index === 0
                                    ? 'text-lg font-medium text-foreground sm:text-xl sm:leading-8'
                                    : 'text-sm sm:text-base'
                            "
                        >
                            {{ paragraph }}
                        </p>
                    </div>
                </MotionReveal>
            </div>
            <div
                v-if="personalDetails.length"
                class="mt-10 border-t border-border pt-7"
            >
                <MotionReveal
                    ><p class="editorial-kicker">Personal details</p>
                    <h3 class="mt-2 text-lg font-semibold tracking-[-0.025em]">
                        At a glance.
                    </h3></MotionReveal
                >
                <dl
                    class="mt-6 grid grid-cols-2 gap-x-6 gap-y-5 sm:grid-cols-3 xl:grid-cols-4"
                >
                    <div
                        v-for="detail in personalDetails"
                        :key="detail.label"
                        class="border-t border-border pt-3"
                    >
                        <dt class="editorial-kicker">{{ detail.label }}</dt>
                        <dd class="mt-1.5 text-sm font-medium">
                            <a
                                v-if="detail.href"
                                :href="detail.href"
                                class="hover:text-primary"
                                >{{ detail.value }}</a
                            ><template v-else>{{ detail.value }}</template>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <section
        v-if="projects.length"
        id="projects"
        class="scroll-mt-2 border-t border-border bg-white py-14 sm:py-16 lg:py-20"
        aria-labelledby="projects-heading"
    >
        <div class="content-container">
            <MotionReveal
                ><div class="mb-7 flex items-end justify-between gap-5">
                    <div>
                        <h2
                            id="projects-heading"
                            class="text-2xl font-semibold tracking-[-0.035em] sm:text-3xl"
                        >
                            Projects
                        </h2>
                    </div>
                    <a
                        href="/projects"
                        class="hidden items-center gap-2 text-sm font-medium hover:text-primary sm:inline-flex"
                        >All Projects <ArrowUpRight class="size-4"
                    /></a></div
            ></MotionReveal>
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                <MotionReveal
                    v-for="(project, index) in projects"
                    :key="project.id"
                    :delay="(index % 2) * 60"
                    ><ProjectCard :project="project" :index="index"
                /></MotionReveal>
            </div>
        </div>
    </section>

    <section
        v-if="skills.length"
        id="skills"
        class="scroll-mt-2 border-t border-border bg-white py-14 sm:py-16 lg:py-20"
        aria-labelledby="skills-heading"
    >
        <div
            class="content-container grid gap-8 lg:grid-cols-[0.34fr_1fr] lg:gap-14"
        >
            <MotionReveal
                ><div>
                    <p class="editorial-kicker">Skills</p>
                    <h2 id="skills-heading" class="editorial-heading mt-3">
                        Capabilities.
                    </h2>
                </div></MotionReveal
            >
            <div class="divide-y divide-border border-t border-border">
                <MotionReveal
                    v-for="(items, category, index) in groupedSkills"
                    :key="category"
                    :delay="Number(index) * 45"
                    ><div class="grid gap-2 py-5 sm:grid-cols-[10rem_1fr]">
                        <h3 class="text-sm font-semibold">{{ category }}</h3>
                        <p class="text-sm leading-7 text-muted-foreground">
                            <template
                                v-for="(skill, skillIndex) in items"
                                :key="skill.id"
                                >{{ skill.name
                                }}<span
                                    v-if="skillIndex < items.length - 1"
                                    class="mx-2 text-border"
                                    >·</span
                                ></template
                            >
                        </p>
                    </div></MotionReveal
                >
            </div>
        </div>
    </section>

    <section
        v-if="educations.length || certificates.length"
        id="education"
        class="scroll-mt-2 border-t border-border bg-white py-14 sm:py-16 lg:py-20"
        aria-labelledby="education-heading"
    >
        <div
            class="content-container grid gap-8 lg:grid-cols-[0.34fr_1fr] lg:gap-14"
        >
            <MotionReveal
                ><div>
                    <p class="editorial-kicker">Education</p>
                    <h2 id="education-heading" class="editorial-heading mt-3">
                        Background.
                    </h2>
                </div></MotionReveal
            >
            <div class="divide-y divide-border border-t border-border">
                <MotionReveal
                    v-for="education in educations"
                    :key="education.id"
                    ><article class="grid gap-3 py-5 sm:grid-cols-[1fr_auto]">
                        <div>
                            <h3 class="font-semibold">
                                {{ education.institution }}
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{
                                    [education.degree, education.field_of_study]
                                        .filter(Boolean)
                                        .join(' · ')
                                }}
                            </p>
                            <p
                                v-if="education.location"
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                {{ education.location }}
                            </p>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ education.start_year
                            }}<template
                                v-if="
                                    education.start_year || education.end_year
                                "
                            >
                                — </template
                            >{{ education.end_year || 'Present' }}
                        </p>
                    </article></MotionReveal
                >
                <MotionReveal
                    v-for="certificate in certificates"
                    :key="`certificate-${certificate.id}`"
                    ><component
                        :is="certificate.credential_url ? 'a' : 'article'"
                        :href="certificate.credential_url || undefined"
                        :target="
                            certificate.credential_url ? '_blank' : undefined
                        "
                        rel="noopener noreferrer"
                        class="group flex items-center justify-between gap-5 py-4"
                        ><div>
                            <h3 class="text-sm font-medium">
                                {{ certificate.title }}
                            </h3>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ certificate.issuer
                                }}<template v-if="certificate.issue_date">
                                    ·
                                    {{
                                        new Date(
                                            certificate.issue_date,
                                        ).getFullYear()
                                    }}</template
                                >
                            </p>
                        </div>
                        <ArrowUpRight
                            v-if="certificate.credential_url"
                            class="size-4 text-muted-foreground group-hover:text-primary" /></component
                ></MotionReveal>
            </div>
        </div>
    </section>

    <section
        v-if="trainings.length"
        id="training"
        class="scroll-mt-2 border-t border-border bg-white py-14 sm:py-16 lg:py-20"
        aria-labelledby="training-heading"
    >
        <div
            class="content-container grid gap-8 lg:grid-cols-[0.34fr_1fr] lg:gap-14"
        >
            <MotionReveal
                ><div>
                    <p class="editorial-kicker">Training</p>
                    <h2 id="training-heading" class="editorial-heading mt-3">
                        Courses.
                    </h2>
                </div></MotionReveal
            >
            <div class="divide-y divide-border border-t border-border">
                <MotionReveal v-for="training in trainings" :key="training.id"
                    ><article class="grid gap-3 py-5 sm:grid-cols-[1fr_auto]">
                        <div>
                            <h3 class="font-semibold">
                                {{ training.program_name }}
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ training.institution
                                }}<template v-if="training.field_of_study">
                                    · {{ training.field_of_study }}</template
                                >
                            </p>
                            <p
                                v-if="training.description"
                                class="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground"
                            >
                                {{ training.description }}
                            </p>
                        </div>
                        <p class="text-xs text-muted-foreground sm:text-right">
                            <span
                                v-if="
                                    trainingPeriod(
                                        training.start_date,
                                        training.end_date,
                                    )
                                "
                                class="block"
                                >{{
                                    trainingPeriod(
                                        training.start_date,
                                        training.end_date,
                                    )
                                }}</span
                            ><span v-if="training.location" class="block">{{
                                training.location
                            }}</span>
                        </p>
                    </article></MotionReveal
                >
            </div>
        </div>
    </section>

    <section
        v-if="experiences.length"
        id="experience"
        class="scroll-mt-2 border-t border-border bg-white py-14 sm:py-16 lg:py-20"
        aria-labelledby="experience-heading"
    >
        <div
            class="content-container grid gap-8 lg:grid-cols-[0.34fr_1fr] lg:gap-14"
        >
            <MotionReveal
                ><div>
                    <p class="editorial-kicker">Experience</p>
                    <h2 id="experience-heading" class="editorial-heading mt-3">
                        Professional path.
                    </h2>
                </div></MotionReveal
            >
            <div class="divide-y divide-border border-t border-border">
                <MotionReveal
                    v-for="experience in experiences"
                    :key="experience.id"
                    ><article class="grid gap-3 py-6 sm:grid-cols-[1fr_auto]">
                        <div>
                            <h3 class="font-semibold">
                                {{ experience.position }}
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ experience.company_name
                                }}<template v-if="experience.employment_type">
                                    · {{ experience.employment_type }}</template
                                >
                            </p>
                            <p
                                v-if="experience.description"
                                class="mt-3 max-w-2xl text-sm leading-6 whitespace-pre-line text-muted-foreground"
                            >
                                {{ experience.description }}
                            </p>
                        </div>
                        <p class="text-xs text-muted-foreground sm:text-right">
                            {{
                                experiencePeriod(
                                    experience.start_date,
                                    experience.end_date,
                                    experience.is_current,
                                )
                            }}<span v-if="experience.location" class="block">{{
                                experience.location
                            }}</span>
                        </p>
                    </article></MotionReveal
                >
            </div>
        </div>
    </section>

    <section
        v-if="contactHref !== '#'"
        id="contact"
        class="scroll-mt-2 bg-[#171717] py-14 text-[#f7f7f5] sm:py-16 lg:py-20"
        aria-labelledby="contact-heading"
    >
        <MotionReveal
            ><div class="content-container">
                <p class="editorial-kicker !text-[#9eb0ff]">Contact</p>
                <h2
                    id="contact-heading"
                    class="mt-3 max-w-3xl text-[clamp(2.25rem,5vw,4.5rem)] leading-[0.98] font-semibold tracking-[-0.05em]"
                >
                    Let's work together.
                </h2>
                <div class="mt-7 flex flex-wrap items-center gap-5">
                    <a
                        :href="contactHref"
                        class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-white hover:text-[#c8d6ff]"
                        >Get in touch <ArrowUpRight class="size-4" /></a
                    ><SocialLinks
                        v-if="socialLinks.length"
                        :links="socialLinks"
                        compact
                    />
                </div></div
        ></MotionReveal>
    </section>
</template>
