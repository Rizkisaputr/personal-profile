export type Skill = {
    id: number;
    name: string;
    slug: string;
    category: string | null;
    icon: string | null;
    proficiency: number | null;
    description: string | null;
    sort_order: number;
    is_active: boolean;
};

export type Experience = {
    id: number;
    company_name: string;
    position: string;
    employment_type: string | null;
    location: string | null;
    start_date: string;
    end_date: string | null;
    is_current: boolean;
    description: string | null;
    sort_order: number;
    is_active: boolean;
};

export type Education = {
    id: number;
    institution: string;
    degree: string | null;
    field_of_study: string | null;
    location: string | null;
    start_year: number | null;
    end_year: number | null;
    description: string | null;
    sort_order: number;
    is_active: boolean;
};

export type Training = {
    id: number;
    institution: string;
    program_name: string;
    field_of_study: string | null;
    location: string | null;
    start_date: string | null;
    end_date: string | null;
    credential_url: string | null;
    description: string | null;
    sort_order: number;
    is_active: boolean;
};

export type Profile = {
    id: number;
    full_name: string;
    professional_title: string | null;
    tagline: string | null;
    short_bio: string | null;
    about: string | null;
    profile_image: string | null;
    profile_image_url: string | null;
    hero_image: string | null;
    hero_image_url: string | null;
    login_image: string | null;
    login_image_url: string | null;
    location: string | null;
    email: string | null;
    phone: string | null;
    whatsapp: string | null;
    birth_place: string | null;
    birth_date: string | null;
    marital_status: string | null;
    availability_status: string | null;
    cv_file: string | null;
    cv_url: string | null;
    cv_name: string | null;
};

export type Technology = {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    projects_count?: number;
};

export type ProjectImage = {
    id: number;
    image: string;
    url: string;
    caption: string | null;
    sort_order: number;
};

export type Project = {
    id: number;
    title: string;
    slug: string;
    short_description: string | null;
    description: string | null;
    thumbnail: string | null;
    thumbnail_url: string | null;
    category: string | null;
    project_date: string | null;
    github_url: string | null;
    demo_url: string | null;
    client_name: string | null;
    work_status: 'waiting' | 'running' | 'on_hold' | 'done';
    is_featured: boolean;
    is_published: boolean;
    sort_order: number;
    seo_title: string | null;
    seo_description: string | null;
    technologies: Technology[];
    technology_ids?: number[];
    images?: ProjectImage[];
};

export type Certificate = {
    id: number;
    title: string;
    issuer: string | null;
    issue_date: string | null;
    credential_id: string | null;
    credential_url: string | null;
    image: string | null;
    image_url?: string | null;
    description: string | null;
    sort_order: number;
    is_active: boolean;
};

export type SocialLink = {
    id: number;
    platform: string;
    label: string | null;
    url: string;
    icon: string | null;
    sort_order: number;
    is_active: boolean;
};

export type FinanceAccount = {
    id: number;
    name: string;
    type: 'bank' | 'cash' | 'e_wallet' | 'other';
    opening_balance: string;
    current_balance?: string;
    currency: string;
    is_active: boolean;
    notes: string | null;
    has_transactions?: boolean;
};

export type FinanceCategory = {
    id: number;
    name: string;
    type: 'income' | 'expense';
    is_active?: boolean;
};
export type FinanceTransaction = {
    id: number;
    type: 'income' | 'expense' | 'transfer' | 'adjustment';
    transaction_date: string;
    amount: string;
    adjustment_direction: 'increase' | 'decrease' | null;
    category_id?: number | null;
    account_id?: number | null;
    from_account_id?: number | null;
    to_account_id?: number | null;
    description: string | null;
    category: { id: number; name: string } | null;
    account: { id: number; name: string } | null;
    from_account: { id: number; name: string } | null;
    to_account: { id: number; name: string } | null;
};
