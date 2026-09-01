import sys
from PIL import Image, ImageDraw, ImageFont
import os

def generate_card(user_name, trust_score, entity_name, output_path):
    # Canvas setup
    width, height = 1200, 675
    background_color = (15, 23, 42) # Dark Maritime Blue
    accent_color = (0, 150, 136) # Teal Secondary
    text_color = (248, 250, 252) # Slate White

    img = Image.new('RGB', (width, height), color=background_color)
    draw = ImageDraw.Draw(img)

    # Drawing background shapes for aesthetic
    draw.rectangle([0, 0, 40, height], fill=accent_color)
    draw.polygon([(0, 0), (width, 0), (width, 100), (0, 300)], fill=(30, 41, 59))

    # Try loading fonts, fallback to default
    try:
        # Assuming fonts are available in the system or we provide them
        font_large = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf", 80)
        font_medium = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 40)
        font_small = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 24)
    except:
        font_large = ImageFont.load_default()
        font_medium = ImageFont.load_default()
        font_small = ImageFont.load_default()

    # Content
    draw.text((100, 100), "TRAVELER IDENTITY", font=font_small, fill=accent_color)
    draw.text((100, 140), user_name.upper(), font=font_large, fill=text_color)
    
    draw.text((100, 300), "TRUST SCORE", font=font_small, fill=accent_color)
    draw.text((100, 340), f"{trust_score}/100", font=font_medium, fill=text_color)

    draw.text((100, 450), "LATEST VERIFIED PRESENCE", font=font_small, fill=accent_color)
    draw.text((100, 490), entity_name, font=font_medium, fill=text_color)

    # Verified Badge
    draw.ellipse([width-250, height-250, width-50, height-50], fill=accent_color)
    draw.text((width-220, height-160), "VERIFIED", font=font_medium, fill=background_color)

    # Save
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    img.save(output_path)
    print(f"Card saved to {output_path}")

if __name__ == "__main__":
    if len(sys.argv) < 5:
        print("Usage: python generate_card.py <user_name> <trust_score> <entity_name> <output_path>")
    else:
        generate_card(sys.argv[1], sys.argv[2], sys.argv[3], sys.argv[4])
