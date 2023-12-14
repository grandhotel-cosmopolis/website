import { ReactElement } from "react";
import { ListElement, ListElementProps } from "./list-element";

type Item<T> = T & ListElementProps;

type ListProps<T> = {
  readonly items: Item<T>[];
  readonly renderItem: (children: ReactElement, key: number) => ReactElement;
};

export const List = <T extends unknown>(props: ListProps<T>) => {
  return (
    <>
      {props.items.map((item, index) =>
        props.renderItem(
          <ListElement
            title={item.title}
            subtitle={item.subtitle}
            body={item.body}
            image={item.image}
          />,
          index
        )
      )}
    </>
  );
};
