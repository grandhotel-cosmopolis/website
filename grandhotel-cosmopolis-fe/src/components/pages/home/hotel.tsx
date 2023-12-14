import { Stack, Box } from "@mui/material";
import { ConversationIcon } from "../../../assets/general/icons/conversation-icon";
import { HeartIcon } from "../../../assets/general/icons/heart-icon";
import { HomeIcon } from "../../../assets/general/icons/home-icon";
import { MoonIcon } from "../../../assets/general/icons/moon-icon";
import { MusicIcon } from "../../../assets/general/icons/music-icon";
import { RoomIcon } from "../../../assets/general/icons/room-icon";
import { ElementWrapper } from "../../shared/element-wrapper";
import HotelImage from "../../../assets/home/hotel.png";
import { useIsTabletView } from "../../hooks/screen-sizes/use-is-tablet-view";
import { IconWithText } from "./hotel/icon-with-text";
import { useTranslation } from "react-i18next";

export const Hotel = () => {
  const isTabletView = useIsTabletView();
  const { t } = useTranslation();
  return (
    <ElementWrapper>
      <Box display="flex">
        <Stack py={10} justifyContent="space-between" height="100%" pr={4}>
          <IconWithText
            icon={<HeartIcon size={isTabletView ? "middle" : "large"} />}
            text={t("home.hotel.heart")}
            align="right"
          />
          <IconWithText
            icon={<MusicIcon size={isTabletView ? "middle" : "large"} />}
            text={t("home.hotel.music")}
            align="right"
          />
          <IconWithText
            icon={<HomeIcon size={isTabletView ? "middle" : "large"} />}
            text={t("home.hotel.home")}
            align="right"
          />
        </Stack>
        <Box flexGrow={1} display="flex">
          <img src={HotelImage} style={{ width: "100%" }} />
        </Box>
        <Stack py={10} justifyContent="space-between" height="100%" pl={4}>
          <IconWithText
            icon={<RoomIcon size={isTabletView ? "middle" : "large"} />}
            text={t("home.hotel.room")}
            align="left"
          />
          <IconWithText
            icon={<ConversationIcon size={isTabletView ? "middle" : "large"} />}
            text={t("home.hotel.conversation")}
            align="left"
          />
          <IconWithText
            icon={<MoonIcon size={isTabletView ? "middle" : "large"} />}
            text={t("home.hotel.moon")}
            align="left"
          />
        </Stack>
      </Box>
    </ElementWrapper>
  );
};
