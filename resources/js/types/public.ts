import type {
    Certificate,
    Education,
    Experience,
    Profile,
    Project,
    Skill,
    SocialLink,
    Training,
} from './admin';

export type PublicProfile = Profile & { cv_url: string | null };
export type PersonalDetail = {
    label: string;
    value: string;
    href?: string | null;
};
export type PublicProject = Project & { url: string };
export type PublicSettings = Record<string, string | null | undefined> & {
    logo_url?: string | null;
    favicon_url?: string | null;
};
export type SeoData = {
    title: string;
    description?: string | null;
    canonical: string;
    image?: string | null;
};
export type HomePageProps = {
    profile: PublicProfile | null;
    personalDetails: PersonalDetail[];
    projects: PublicProject[];
    skills: Skill[];
    experiences: Experience[];
    educations: Education[];
    trainings: Training[];
    certificates: Certificate[];
    socialLinks: SocialLink[];
    sidebarSocialLinks: SocialLink[];
    settings: PublicSettings;
    seo: SeoData;
};
