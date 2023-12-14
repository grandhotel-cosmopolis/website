import WeNeedUs from "../../../assets/home/weNeedUs.jpg";
import Anniversary from "../../../assets/home/anniversary.jpg";
import United from "../../../assets/home/united.jpg";
import { List } from "../../shared/card-list/list";
import { ReactElement } from "react";
import { ElementWrapper } from "../../shared/element-wrapper";
import { useTranslation } from "react-i18next";

type NewsContent = {
  title: string;
  subtitle?: string;
  image: string;
  body: string;
};

export const News = () => {
  const { t } = useTranslation();
  const news: NewsContent[] = [
    {
      title: t("home.news.weNeedUs.title"),
      body: t("home.news.weNeedUs.body"),
      image: WeNeedUs,
    },
    {
      title: t("home.news.anniversary.title"),
      subtitle: t("home.news.anniversary.subtitle"),
      body: t("home.news.anniversary.body"),
      image: Anniversary,
    },
    {
      title: t("home.news.united.title"),
      subtitle: t("home.news.united.subtitle"),
      body: t("home.news.united.body"),
      image: United,
    },
  ];
  return (
    <List
      items={news}
      renderItem={(children: ReactElement, key: number) => (
        <ElementWrapper key={key}>{children}</ElementWrapper>
      )}
    />
  );
};
