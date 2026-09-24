/**
 * Photography, imported so Astro can resize and re-encode it (AVIF/WebP) at build.
 *
 * All images are placeholders from Pexels (https://www.pexels.com/license/, free for
 * commercial use, no attribution required). The Pexels ID is noted beside each so
 * the source can be traced. Replace with the client's own photos when available:
 * drop the file into src/assets/photos/ with the same name and rebuild.
 */
import sceneLiving from '../assets/photos/scene-living.jpg'; // 29012619
import sceneKitchen from '../assets/photos/scene-kitchen.jpg'; // 7166645
import sceneLoft from '../assets/photos/scene-loft.jpg'; // 28456460
import sceneBath from '../assets/photos/scene-bath.jpg'; // 7045908
import sceneLight from '../assets/photos/scene-light.jpg'; // 35523270
import detailSink from '../assets/photos/detail-sink.jpg'; // 5904036
import aboutTeam from '../assets/photos/about-team.jpg'; // 6195125
import residential from '../assets/photos/service-residential-cleaning.jpg'; // 6197116
import office from '../assets/photos/service-office-cleaning.jpg'; // 10567271
import deep from '../assets/photos/service-deep-cleaning.jpg'; // 9462746
import commercial from '../assets/photos/service-commercial-cleaning.jpg'; // 34516670
import windowCleaning from '../assets/photos/service-window-cleaning.jpg'; // 4440537
import moveIn from '../assets/photos/service-move-in-cleaning.jpg'; // 7031621
import general from '../assets/photos/service-general-cleaning.jpg'; // 6195118
import emergency from '../assets/photos/service-emergency-cleaning.jpg'; // 6196685

export const photos = {
  'scene-living': { src: sceneLiving, alt: 'A bright, airy living room with white sofas, sheer floor-to-ceiling curtains and glass pendant lights' },
  'scene-kitchen': { src: sceneKitchen, alt: 'A spotless cream kitchen with a round white dining table and glass-fronted cabinets' },
  'scene-loft': { src: sceneLoft, alt: 'A clean open-plan loft with white walls, a mezzanine balcony and framed artwork' },
  'scene-bath': { src: sceneBath, alt: 'A luxury bathroom with twin basins on a wooden vanity, a freestanding tub and neutral stone finishes' },
  'scene-light': { src: sceneLight, alt: 'Afternoon sunlight casting window-pane shadows across a clean cream wall' },
  'detail-sink': { src: detailSink, alt: 'Hands rinsing a white plate at a spotless marble countertop sink' },
  'about-team': { src: aboutTeam, alt: 'Three cleaners in red uniforms standing with a vacuum, mop and equipment in a bright modern living room' },
  'service-residential-cleaning': { src: residential, alt: 'A cleaner in a red uniform mopping the floor of a modern open-plan home while a colleague cleans the window' },
  'service-office-cleaning': { src: office, alt: 'A person wiping a wooden office desk with a cloth and a bottle of multi-purpose cleaner' },
  'service-deep-cleaning': { src: deep, alt: 'A professional cleaner in rubber gloves detail-cleaning a kitchen stove with spray and a cloth' },
  'service-commercial-cleaning': { src: commercial, alt: 'A cleaner working from a janitorial cart in a bright, spacious commercial hallway' },
  'service-window-cleaning': { src: windowCleaning, alt: 'A cleaner in red gloves spraying and wiping an interior window pane' },
  'service-move-in-cleaning': { src: moveIn, alt: 'A bright, empty apartment room with a polished wooden floor, cleaned and ready to move into' },
  'service-general-cleaning': { src: general, alt: 'A cleaner in a red uniform working with a bucket of supplies in a bright modern room' },
  'service-emergency-cleaning': { src: emergency, alt: 'A cleaning crew in red uniforms carrying equipment up to a house, arriving for a call-out' },
};

/** The photo for a service slug. */
export const servicePhoto = (slug) => photos[`service-${slug}`];
