export type ContactPageProps = {
    readonly name: string;
    readonly phone: string;
    readonly email: string;
    readonly registrationPhone: string;
    readonly registrationEmail: string;
};
export type PhotosPageProps = {
    readonly title: string;
    readonly caption: string;
    readonly photos: string[];
};
export type SpeakerPageProps = {
    readonly name: string;
    readonly photo: string;
    readonly bio: string[];
};
export type Techno = {
    readonly id: number;
    readonly name: string;
    readonly url: string | null;
};
export type User = {
    readonly id: number;
    readonly email: string;
    readonly roles: string[];
    readonly isVerified: boolean;
};
export type WorkshopSlotDto = {
    readonly day: string;
    readonly hours: string;
    readonly place: string;
};
export type WorkshopsPageProps = {
    readonly intro: string;
    readonly slots: WorkshopSlotDto[];
    readonly schedulingNotes: string[];
    readonly content: string[];
    readonly price: string;
    readonly priceNote: string;
};
