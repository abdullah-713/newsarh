#!/bin/bash

# Security Patch Script for SARH System
# Updates all Filament Resources to use SecureResource base class

echo "🔐 Starting Security Patch for SARH Resources..."
echo ""

# Array of resources with their permission prefixes
declare -A resources=(
    ["BranchResource"]="branches"
    ["BadgeResource"]="gamification"
    ["RewardResource"]="rewards"
    ["ChallengeResource"]="gamification"
    ["GeofenceResource"]="attendance"
    ["AnnouncementResource"]="settings"
    ["WorkShiftResource"]="shifts"
    ["ShiftTemplateResource"]="shifts"
    ["UserShiftAssignmentResource"]="shifts"
    ["OfficialHolidayResource"]="settings"
    ["TrapConfigurationResource"]="traps"
    ["TrapLogResource"]="traps"
    ["IntegrityReportResource"]="integrity"
)

RESOURCES_DIR="/workspaces/newsarh/app/Filament/Resources"

for resource in "${!resources[@]}"; do
    permission="${resources[$resource]}"
    file="$RESOURCES_DIR/$resource.php"
    
    if [ ! -f "$file" ]; then
        echo "⚠️  File not found: $resource"
        continue
    fi
    
    # Backup original
    cp "$file" "$file.backup"
    
    # Update imports
    sed -i "s/use Filament\\\\Resources\\\\Resource;/use App\\\\Filament\\\\Resources\\\\SecureResource;/" "$file"
    
    # Update class extends
    sed -i "s/class $resource extends Resource/class $resource extends SecureResource/" "$file"
    
    # Add Model import if not exists
    if ! grep -q "use Illuminate\\\\Database\\\\Eloquent\\\\Model;" "$file"; then
        sed -i "/use Illuminate\\\\Database\\\\Eloquent\\\\Builder;/a use Illuminate\\\\Database\\\\Eloquent\\\\Model;" "$file"
    fi
    
    # Add permission prefix after model declaration
    if ! grep -q "protected static ?\$permissionPrefix" "$file"; then
        sed -i "/protected static ?\\\$model/a\\    \\n    protected static ?string \\\$permissionPrefix = '$permission';" "$file"
    fi
    
    echo "✅ Updated: $resource (permission: $permission)"
done

echo ""
echo "🎉 Security patch completed!"
echo "📝 Backups saved with .backup extension"
