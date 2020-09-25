// @flow
import * as React from 'react';
import cn from 'classnames';
import { Container } from './ImageSlider.style';
import type { SettingsSlider } from '~/types';

type Image = {|
  url: string,
  alt: string,
|};

export type Props = {
  images: Image[],
  settingsSlider: SettingsSlider,
  className?: string,
};

const ImageSlider = ({ images, className, settingsSlider }: Props) => (
  <Container {...settingsSlider} className={cn('image-slider', className)}>
    {images.map((img, idx) => (
      <div key={`image-slide-${idx}`} className="image-slide">
        <img src={img.url} alt={img.alt} />
      </div>
    ))}
  </Container>
);

export default ImageSlider;
